<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\{CommissionCard, CardModification, ActivityLog};
use Illuminate\Http\{Request, JsonResponse};
use Illuminate\Support\Facades\{DB, Validator};
use Carbon\Carbon;

class CommissionCardController extends Controller
{
    // modifications.modifiedBy removed — HasMany on list = N+1 per card
    private array $with = [
        'branch','accountType','accountStatus','tradingType',
        'broker','marketer','extMarketer1','extMarketer2',
        'createdBy',
    ];

    // ── BRANCH ISOLATION: hard-lock branch managers to own branch ──
    private function applyBranchScope($query, Request $request)
    {
        $user = $request->user();
        if ($user->isBranchManager()) {
            $query->where(function($q) use ($user) {
                $q->where(function($q1) use ($user) {
                    $q1->where('branch_id', $user->branch_id)
                       ->where(function($q2) {
                           $q2->whereNull('cc_branch_id')
                              ->orWhereIn('cc_status', ['accepted','completed','rejected']);
                       });
                })
                ->orWhere('cc_branch_id', $user->branch_id);
            });
        } elseif ($user->isFinanceAdmin() && $request->branch_id) {
            $query->forBranch((int)$request->branch_id);
        }
        return $query;
    }

    private function assertOwnership(CommissionCard $card, Request $request): void
    {
        $user = $request->user();
        if ($user->isBranchManager() && (int)$card->branch_id !== (int)$user->branch_id) {
            abort(403, 'Access denied: this card does not belong to your branch.');
        }
    }

    // GET /api/cards
    public function index(Request $request): JsonResponse
    {
        $query = CommissionCard::with($this->with);
        $this->applyBranchScope($query, $request);

        if ($m  = $request->month)        $query->forMonth($m);
        if ($br = $request->broker_id)    $query->forBroker((int)$br);
        if ($s  = $request->status)       $query->where('status', $s);
        if ($k  = $request->kind)         $query->where('account_kind', $k);
        if ($q  = $request->search)       $query->search($q);
        if ($request->modified_only)      $query->modified();
        if ($min = $request->min_deposit) $query->where('monthly_deposit', '>=', (float)$min);

        $perPage = min((int)($request->per_page ?? 50), 200);
        $cards   = $query->orderBy('month_date', 'desc')->orderBy('account_number')->paginate($perPage);

        return response()->json(['success' => true, 'data' => $cards, 'summary' => $this->buildSummary($request->user())]);
    }

    // GET /api/cards/{id}
    public function show(Request $request, int $id): JsonResponse
    {
        // Load modifications only on single-card view
        $card = CommissionCard::with(array_merge($this->with, ['modifications.modifiedBy']))->findOrFail($id);
        $this->assertOwnership($card, $request);
        return response()->json(['success' => true, 'data' => $card]);
    }

    // POST /api/cards
    public function store(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), $this->validationRules());
        if ($v->fails()) return response()->json(['success' => false, 'errors' => $v->errors()], 422);

        $user = $request->user();

        $branchId = $user->isBranchManager()
            ? $user->branch_id
            : ($request->branch_id ? (int)$request->branch_id : null);

        if (CommissionCard::where('account_number', $request->account_number)
                          ->where('month', $request->month)
                          ->whereNull('deleted_at')->exists()) {
            return response()->json(['success' => false, 'message' => "Account #{$request->account_number} already exists for {$request->month}."], 409);
        }

        $card = DB::transaction(function() use ($request, $branchId, $user) {
            $fields               = $this->extractFields($request);
            $fields['branch_id']  = $branchId;
            $fields['status']     = 'new_added';
            $fields['created_by'] = $user->id;
            $card = CommissionCard::create($fields);
            ActivityLog::record('create_card', $card, ['account' => $card->account_number, 'branch_id' => $branchId]);
            return $card;
        });

        return response()->json(['success' => true, 'message' => "Card #{$card->account_number} created.", 'data' => $card->load($this->with)], 201);
    }

    // PUT /api/cards/{id}
    public function update(Request $request, int $id): JsonResponse
    {
        $card = CommissionCard::findOrFail($id);
        $this->assertOwnership($card, $request);

        $v = Validator::make($request->all(), array_merge($this->validationRules(false),
            ['reason' => 'required|string|max:200', 'notes' => 'nullable|string|max:2000']));
        if ($v->fails()) return response()->json(['success' => false, 'errors' => $v->errors()], 422);

        DB::transaction(function() use ($request, $card) {
            $oldData = $card->only(['broker_id','broker_commission','marketer_id','marketer_commission',
                'ext_marketer1_id','ext_commission1','ext_marketer2_id','ext_commission2',
                'initial_deposit','monthly_deposit','account_status_id','account_kind','notes']);

            $fields = $this->extractFields($request);
            if ($request->user()->isBranchManager()) unset($fields['branch_id']);

            $card->update(array_merge($fields, ['status' => 'modified']));
            $newData = $card->fresh()->only(array_keys($oldData));

            CardModification::create([
                'card_id'        => $card->id,
                'account_number' => $card->account_number,
                'month'          => $card->month,
                'reason'         => $request->reason,
                'notes'          => $request->notes,
                'old_data'       => $oldData,
                'new_data'       => $newData,
                'modified_by'    => $request->user()->id,
                'modified_at'    => now(),
            ]);
            ActivityLog::record('edit_card', $card, ['reason' => $request->reason, 'account' => $card->account_number]);
        });

        return response()->json(['success' => true, 'message' => "Card #{$card->account_number} updated.", 'data' => $card->fresh($this->with)]);
    }

    // DELETE /api/cards/{id}  — Finance Admin only
    public function destroy(Request $request, int $id): JsonResponse
    {
        if (!$request->user()->isFinanceAdmin())
            return response()->json(['success' => false, 'message' => 'Finance Admin only.'], 403);

        $card = CommissionCard::findOrFail($id);
        $card->update(['status' => 'inactive']);
        $card->delete();
        ActivityLog::record('delete_card', $card);
        return response()->json(['success' => true, 'message' => "Card #{$card->account_number} deleted."]);
    }

    // GET /api/cards/tree
    public function tree(Request $request): JsonResponse
    {
        if (!$request->month) {
            return response()->json(['success' => false, 'message' => 'month parameter is required.'], 422);
        }

        $user    = $request->user();
        $query   = CommissionCard::with(['broker','marketer','extMarketer1','extMarketer2','branch']);
        $this->applyBranchScope($query, $request);
        $query->forMonth($request->month);

        $cards   = $query->orderBy('account_number')->get();
        $groupBy = $request->group_by ?? 'broker';

        $tree = match($groupBy) {
            'branch'       => $this->groupByBranch($cards),
            'month'        => $this->groupByMonth($cards),
            'ext_marketer' => $this->groupByExtMarketer($cards),
            default        => $this->groupByBroker($cards),
        };

        return response()->json([
            'success'      => true,
            'group_by'     => $groupBy,
            'branch_scope' => $user->isBranchManager() ? ($user->branch?->name_ar ?? 'فرعك') : 'جميع الفروع',
            'summary' => [
                'total_accounts'    => $cards->count(),
                'total_initial'     => round($cards->sum('initial_deposit'), 2),
                'total_monthly'     => round($cards->sum('monthly_deposit'), 2),
                'total_broker_comm' => round($cards->sum('broker_commission'), 2),
                'total_mkt_comm'    => round($cards->sum('marketer_commission'), 2),
                'total_ext1_comm'   => round($cards->sum('ext_commission1'), 2),
                'total_ext2_comm'   => round($cards->sum('ext_commission2'), 2),
                'total_all_comm'    => round($cards->sum(fn($c) => $c->broker_commission + $c->marketer_commission + $c->ext_commission1 + $c->ext_commission2), 2),
                'modified_count'    => $cards->where('status', 'modified')->count(),
            ],
            'tree' => $tree,
        ]);
    }

    // GET /api/cards/report
    public function report(Request $request): JsonResponse
    {
        $user = $request->user();

        $base = CommissionCard::query();
        $this->applyBranchScope($base, $request);

        if ($from = $request->month_from) { try { $base->whereDate('month_date', '>=', Carbon::parse("01 {$from}")); } catch(\Exception $e){} }
        if ($to   = $request->month_to)   { try { $base->whereDate('month_date', '<=', Carbon::parse("01 {$to}")->endOfMonth()); } catch(\Exception $e){} }
        if ($br   = $request->broker_id)   $base->forBroker((int)$br);
        if ($s    = $request->status)       $base->where('status', $s);
        if ($k    = $request->kind)         $base->where('account_kind', $k);
        if ($min  = $request->min_deposit)  $base->where('initial_deposit', '>=', (float)$min);

        // Single aggregate query — no PHP-level collection scan
        $agg = (clone $base)->selectRaw(
            'COUNT(*) as total,
             COALESCE(SUM(initial_deposit), 0)     as total_initial_deposit,
             COALESCE(SUM(monthly_deposit), 0)     as total_monthly_deposit,
             COALESCE(SUM(broker_commission), 0)   as total_broker_comm,
             COALESCE(SUM(marketer_commission), 0) as total_marketer_comm,
             COALESCE(SUM(ext_commission1), 0)     as total_ext1_comm,
             COALESCE(SUM(ext_commission2), 0)     as total_ext2_comm,
             SUM(status = "modified")              as modified_count,
             SUM(status = "new_added")             as new_added_count'
        )->first();

        $perPage = min((int)($request->per_page ?? 100), 500);
        $data    = $base->with(['branch','broker','marketer','extMarketer1','extMarketer2'])
                        ->orderBy('month_date', 'desc')->orderBy('account_number')
                        ->paginate($perPage);

        return response()->json([
            'success'      => true,
            'count'        => (int)$agg->total,
            'branch_scope' => $user->isBranchManager() ? ($user->branch?->name_ar ?? 'فرعك') : 'جميع الفروع',
            'summary' => [
                'total_initial_deposit' => round((float)$agg->total_initial_deposit, 2),
                'total_monthly_deposit' => round((float)$agg->total_monthly_deposit, 2),
                'total_broker_comm'     => round((float)$agg->total_broker_comm, 2),
                'total_marketer_comm'   => round((float)$agg->total_marketer_comm, 2),
                'total_ext1_comm'       => round((float)$agg->total_ext1_comm, 2),
                'total_ext2_comm'       => round((float)$agg->total_ext2_comm, 2),
                'modified_count'        => (int)$agg->modified_count,
                'new_added_count'       => (int)$agg->new_added_count,
            ],
            'data' => $data,
        ]);
    }

    // GET /api/cards/modifications
    public function modifications(Request $request): JsonResponse
    {
        $user  = $request->user();
        $query = CardModification::with(['card.branch', 'modifiedBy'])->latest('modified_at');

        // Join is ~10x faster than whereHas for branch-scoped filtering
        if ($user->isBranchManager() && $user->branch_id) {
            $query->join('commission_cards as cc', 'card_modifications.card_id', '=', 'cc.id')
                  ->where('cc.branch_id', $user->branch_id)
                  ->select('card_modifications.*');
        }

        if ($ac = $request->account_number) $query->where('card_modifications.account_number', $ac);

        return response()->json(['success' => true, 'data' => $query->paginate(50)]);
    }

    // ── Helpers ──
    private function validationRules(bool $required = true): array
    {
        $r = $required ? 'required' : 'sometimes|required';
        return [
            'account_number'    => "{$r}|string|max:30",
            'month'             => "{$r}|string|max:20",
            'month_date'        => "{$r}|date",
            'branch_id'         => 'nullable|exists:branches,id',
            'account_type_id'   => 'nullable|exists:account_types,id',
            'account_status_id' => 'nullable|exists:account_statuses,id',
            'trading_type_id'   => 'nullable|exists:trading_types,id',
            'account_kind'      => 'nullable|in:new,sub',
            'broker_id'         => 'nullable|exists:employees,id',
            'broker_commission' => 'nullable|numeric|min:0',
            'marketer_id'       => 'nullable|exists:employees,id',
            'marketer_commission'  => 'nullable|numeric|min:0',
            'ext_marketer1_id'  => 'nullable|exists:employees,id',
            'ext_commission1'   => 'nullable|numeric|min:0',
            'ext_marketer2_id'  => 'nullable|exists:employees,id',
            'ext_commission2'   => 'nullable|numeric|min:0',
            'forex_commission'  => 'nullable|numeric|min:0',
            'futures_commission'=> 'nullable|numeric|min:0',
            'initial_deposit'   => 'nullable|numeric|min:0',
            'monthly_deposit'   => 'nullable|numeric|min:0',
            'notes'             => 'nullable|string|max:2000',
        ];
    }

    private function extractFields(Request $request): array
    {
        return $request->only([
            'account_number','month','month_date','branch_id',
            'account_type_id','account_status_id','trading_type_id','account_kind',
            'broker_id','broker_commission','marketer_id','marketer_commission',
            'ext_marketer1_id','ext_commission1','ext_marketer2_id','ext_commission2',
            'forex_commission','futures_commission','initial_deposit','monthly_deposit','notes',
        ]);
    }

    // Uses DB aggregates — never loads all rows into PHP memory
    private function buildSummary($user): array
    {
        $q = CommissionCard::query();
        if ($user->isBranchManager() && $user->branch_id) $q->forBranch($user->branch_id);

        $agg = $q->selectRaw(
            'COUNT(*) as total,
             COALESCE(SUM(initial_deposit), 0) as initial_deposit,
             COALESCE(SUM(monthly_deposit), 0) as monthly_deposit,
             SUM(status = "modified")          as modified,
             SUM(status = "new_added")         as new_added'
        )->first();

        return [
            'total'           => (int)$agg->total,
            'initial_deposit' => round((float)$agg->initial_deposit, 2),
            'monthly_deposit' => round((float)$agg->monthly_deposit, 2),
            'modified'        => (int)$agg->modified,
            'new_added'       => (int)$agg->new_added,
        ];
    }

    private function groupByBroker($cards): array
    {
        return $cards->groupBy(fn($c) => $c->broker?->name ?? 'Unknown')
            ->map(fn($g, $k) => [
                'group_key'       => $k,
                'group_icon'      => '🧑‍💼',
                'count'           => $g->count(),
                'initial_deposit' => round($g->sum('initial_deposit'), 2),
                'monthly_deposit' => round($g->sum('monthly_deposit'), 2),
                'total_comm'      => round($g->sum(fn($c) => $c->broker_commission + $c->marketer_commission + $c->ext_commission1 + $c->ext_commission2), 2),
                'modified_count'  => $g->where('status', 'modified')->count(),
                'cards'           => $g->values(),
            ])->values()->toArray();
    }

    private function groupByBranch($cards): array
    {
        return $cards->groupBy(fn($c) => $c->branch?->name_ar ?? 'غير محدد')
            ->map(fn($g, $k) => [
                'group_key'       => $k,
                'group_icon'      => '🏢',
                'count'           => $g->count(),
                'initial_deposit' => round($g->sum('initial_deposit'), 2),
                'monthly_deposit' => round($g->sum('monthly_deposit'), 2),
                'total_comm'      => round($g->sum(fn($c) => $c->broker_commission + $c->marketer_commission + $c->ext_commission1 + $c->ext_commission2), 2),
                'modified_count'  => $g->where('status', 'modified')->count(),
                'cards'           => $g->values(),
            ])->values()->toArray();
    }

    private function groupByMonth($cards): array
    {
        return $cards->groupBy('month')
            ->map(fn($g, $k) => [
                'group_key'       => $k,
                'group_icon'      => '📅',
                'count'           => $g->count(),
                'initial_deposit' => round($g->sum('initial_deposit'), 2),
                'monthly_deposit' => round($g->sum('monthly_deposit'), 2),
                'total_comm'      => round($g->sum(fn($c) => $c->broker_commission + $c->marketer_commission + $c->ext_commission1 + $c->ext_commission2), 2),
                'modified_count'  => $g->where('status', 'modified')->count(),
                'cards'           => $g->values(),
            ])->values()->toArray();
    }

    private function groupByExtMarketer($cards): array
    {
        return $cards->groupBy(fn($c) => $c->extMarketer1?->name ?? $c->extMarketer2?->name ?? 'بدون مسوّق خارجي')
            ->map(fn($g, $k) => [
                'group_key'      => $k,
                'group_icon'     => '🌐',
                'count'          => $g->count(),
                'initial_deposit'=> round($g->sum('initial_deposit'), 2),
                'monthly_deposit'=> round($g->sum('monthly_deposit'), 2),
                'total_ext_comm' => round($g->sum(fn($c) => $c->ext_commission1 + $c->ext_commission2), 2),
                'modified_count' => $g->where('status', 'modified')->count(),
                'cards'          => $g->values(),
            ])->values()->toArray();
    }
}
