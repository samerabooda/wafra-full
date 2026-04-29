<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\{CommissionCard, CardModification, ActivityLog};
use Illuminate\Http\{Request, JsonResponse};
use Illuminate\Support\Facades\{DB, Validator};
use Carbon\Carbon;

class CommissionCardController extends Controller
{
    private array $with = [
        'branch','accountType','accountStatus','tradingType',
        'broker','marketer','extMarketer1','extMarketer2',
        'ccAgent','ccBranch',
        'modifications.modifiedBy','createdBy',
    ];

    // ── BRANCH ISOLATION: hard-lock branch managers to own branch ──
    private function applyBranchScope($query, Request $request)
    {
        $user = $request->user();
        if ($user->isBranchManager()) {
            $query->where(function($q) use ($user) {
                // Own branch regular cards (all statuses)
                $q->where(function($q1) use ($user) {
                    $q1->where('branch_id', $user->branch_id)
                       ->where(function($q2) {
                           // Exclude CC cards in intermediate states (shown via /callcenter/pending)
                           $q2->whereNull('cc_branch_id')
                              ->orWhereIn('cc_status', ['accepted','completed','rejected']);
                       });
                })
                // CC branch sees ALL cards IT sent (any status)
                ->orWhere('cc_branch_id', $user->branch_id);
            });
        } elseif ($user->isFinanceAdmin() && $request->branch_id) {
            $query->forBranch((int)$request->branch_id);
        }
        return $query;
    }

    // ── Verify branch manager owns a specific card ──
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

        if ($m  = $request->month)       $query->forMonth($m);
        if ($br = $request->broker_id)   $query->forBroker((int)$br);
        if ($s  = $request->status)      $query->where('status',$s);
        if ($k  = $request->kind)        $query->where('account_kind',$k);
        if ($q  = $request->search)      $query->search($q);
        if ($request->modified_only)     $query->modified();
        if ($min = $request->min_deposit) $query->where('monthly_deposit','>=',(float)$min);

        $perPage = min((int)($request->per_page ?? 50), 200);
        $cards   = $query->orderBy('month_date','desc')->orderBy('account_number')->paginate($perPage);

        return response()->json(['success'=>true,'data'=>$cards,'summary'=>$this->buildSummary($request->user())]);
    }

    // GET /api/cards/{id}
    public function show(Request $request, int $id): JsonResponse
    {
        $card = CommissionCard::with($this->with)->findOrFail($id);
        $this->assertOwnership($card, $request);
        return response()->json(['success'=>true,'data'=>$card]);
    }

    // POST /api/cards
   
        // ── Server-side commission limit check ──────────────
        $hasRebate    = (bool)($request->has_rebate ?? false);
        $rebateLimit  = (float) \App\Models\Setting::get('rebate_commission_limit', 7.00);
        $normalLimit  = (float) \App\Models\Setting::get('commission_limit_amount', 8.00);
        $limitEnabled = (bool) \App\Models\Setting::get('commission_limit_enabled', true);
        $limitAmount  = $hasRebate ? $rebateLimit : $normalLimit;

        $commTotal = (float)($request->broker_commission ?? 0)
                   + (float)($request->marketer_commission ?? 0)
                   + (float)($request->ext_commission1 ?? 0)
                   + (float)($request->ext_commission2 ?? 0)
                   + (float)($request->referral_commission ?? 0)
                   + (float)($request->rebate_amount ?? 0);

        $warningCount = (int) $request->header('X-Commission-Warning-Count', 0);
        $maxWarnings  = (int) \App\Models\Setting::get('commission_warning_count', 3);

        if ($limitEnabled && $commTotal > $limitAmount) {
            if ($warningCount >= $maxWarnings) {
                return response()->json([
                    'success'    => false,
                    'blocked'    => true,
                    'total'      => $commTotal,
                    'limit'      => $limitAmount,
                    'has_rebate' => $hasRebate,
                    'message'    => "تجاوزت العمولات الحد المسموح ({$limitAmount}). يرجى التواصل مع المدير المالي.",
                ], 422);
            }
            return response()->json([
                'success'        => false,
                'warning'        => true,
                'warning_number' => $warningCount + 1,
                'warnings_left'  => $maxWarnings - $warningCount - 1,
                'total'          => $commTotal,
                'limit'          => $limitAmount,
                'has_rebate'     => $hasRebate,
                'message'        => ($hasRebate ? "[Rebate حد $7] " : "") . "تحذير: إجمالي العمولات ({$commTotal}) يتجاوز الحد ({$limitAmount}). هل تريد المتابعة؟",
                'can_override'   => true,
            ], 422);
        }

 public function store(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), $this->validationRules());
        if ($v->fails()) return response()->json(['success'=>false,'errors'=>$v->errors()],422);

        $user = $request->user();

        // Branch managers are FORCED to their own branch
        $branchId = $user->isBranchManager()
            ? $user->branch_id
            : ($request->branch_id ? (int)$request->branch_id : null);

        if (CommissionCard::where('account_number',$request->account_number)
                          ->where('month',$request->month)
                          ->whereNull('deleted_at')->exists()) {
            return response()->json(['success'=>false,'message'=>"Account #{$request->account_number} already exists for {$request->month}."],409);
        }

        $card = DB::transaction(function() use ($request, $branchId, $user) {
            $fields             = $this->extractFields($request);
            $fields['branch_id']  = $branchId;
            $fields['status']     = 'new_added';
            $fields['created_by'] = $user->id;
            $card = CommissionCard::create($fields);
            ActivityLog::record('create_card', $card, ['account'=>$card->account_number,'branch_id'=>$branchId]);
            return $card;
        });

        return response()->json(['success'=>true,'message'=>"Card #{$card->account_number} created.",'data'=>$card->load($this->with)],201);
    }

    // PUT /api/cards/{id}
    public function update(Request $request, int $id): JsonResponse
    {
        $card = CommissionCard::findOrFail($id);
        $this->assertOwnership($card, $request);

        $v = Validator::make($request->all(), array_merge($this->validationRules(false),
            ['reason'=>'required|string|max:200','notes'=>'nullable|string|max:2000']));
        if ($v->fails()) return response()->json(['success'=>false,'errors'=>$v->errors()],422);

        DB::transaction(function() use ($request, $card) {
            $oldData = $card->only(['broker_id','broker_commission','marketer_id','marketer_commission',
                'ext_marketer1_id','ext_commission1','ext_marketer2_id','ext_commission2',
            'has_rebate','rebate_amount','referral_account','referral_commission',
                'initial_deposit','monthly_deposit','account_status_id','account_kind','notes']);

            $fields = $this->extractFields($request);
            // Branch managers cannot relocate a card to another branch
            if ($request->user()->isBranchManager()) unset($fields['branch_id']);

            $card->update(array_merge($fields, ['status'=>'modified']));
            $newData = $card->fresh()->only(array_keys($oldData));

            CardModification::create([
                'card_id'=>$card->id,'account_number'=>$card->account_number,'month'=>$card->month,
                'reason'=>$request->reason,'notes'=>$request->notes,
                'old_data'=>$oldData,'new_data'=>$newData,
                'modified_by'=>$request->user()->id,'modified_at'=>now(),
            ]);
            ActivityLog::record('edit_card', $card, ['reason'=>$request->reason,'account'=>$card->account_number]);
        });

        return response()->json(['success'=>true,'message'=>"Card #{$card->account_number} updated.",'data'=>$card->fresh($this->with)]);
    }

    // DELETE /api/cards/{id}  — Finance Admin only
    public function destroy(Request $request, int $id): JsonResponse
    {
        if (!$request->user()->isFinanceAdmin())
            return response()->json(['success'=>false,'message'=>'Finance Admin only.'],403);

        $card = CommissionCard::findOrFail($id);
        $card->update(['status'=>'inactive']);
        $card->delete();
        ActivityLog::record('delete_card', $card);
        return response()->json(['success'=>true,'message'=>"Card #{$card->account_number} deleted."]);
    }

    // GET /api/cards/tree
    public function tree(Request $request): JsonResponse
    {
        $user  = $request->user();
        $query = CommissionCard::with(['broker','marketer','extMarketer1','extMarketer2','branch']);
        $this->applyBranchScope($query, $request);
        if ($m = $request->month) $query->forMonth($m);

        $cards   = $query->orderBy('month_date','desc')->orderBy('account_number')->get();
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
                'total_initial'     => round($cards->sum('initial_deposit'),2),
                'total_monthly'     => round($cards->sum('monthly_deposit'),2),
                'total_broker_comm' => round($cards->sum('broker_commission'),2),
                'total_mkt_comm'    => round($cards->sum('marketer_commission'),2),
                'total_ext1_comm'   => round($cards->sum('ext_commission1'),2),
                'total_ext2_comm'   => round($cards->sum('ext_commission2'),2),
                'total_all_comm'    => round($cards->sum(fn($c) => $c->broker_commission+$c->marketer_commission+$c->ext_commission1+$c->ext_commission2+$c->referral_commission+$c->rebate_amount),2),
                'modified_count'    => $cards->where('status','modified')->count(),
            ],
            'tree' => $tree,
        ]);
    }

    // GET /api/cards/report
    public function report(Request $request): JsonResponse
    {
        $user  = $request->user();
        $query = CommissionCard::with(['broker','marketer','extMarketer1','extMarketer2','branch']);
        $this->applyBranchScope($query, $request);

        if ($from = $request->month_from) { try { $query->whereDate('month_date','>=',Carbon::parse("01 {$from}")); } catch(\Exception $e){} }
        if ($to   = $request->month_to)   { try { $query->whereDate('month_date','<=',Carbon::parse("01 {$to}")->endOfMonth()); } catch(\Exception $e){} }
        if ($br   = $request->broker_id)   $query->forBroker((int)$br);
        if ($s    = $request->status)       $query->where('status',$s);
        if ($k    = $request->kind)         $query->where('account_kind',$k);
        if ($min  = $request->min_deposit)  $query->where('initial_deposit','>=',(float)$min);

        $data = $query->orderBy('month_date','desc')->orderBy('account_number')->get();

        return response()->json([
            'success'      => true,
            'count'        => $data->count(),
            'branch_scope' => $user->isBranchManager() ? ($user->branch?->name_ar ?? 'فرعك') : 'جميع الفروع',
            'summary' => [
                'total_initial_deposit' => round($data->sum('initial_deposit'),2),
                'total_monthly_deposit' => round($data->sum('monthly_deposit'),2),
                'total_broker_comm'     => round($data->sum('broker_commission'),2),
                'total_marketer_comm'   => round($data->sum('marketer_commission'),2),
                'total_ext1_comm'       => round($data->sum('ext_commission1'),2),
                'total_ext2_comm'       => round($data->sum('ext_commission2'),2),
                'modified_count'        => $data->where('status','modified')->count(),
                'new_added_count'       => $data->where('status','new_added')->count(),
            ],
            'data' => $data,
        ]);
    }

    // GET /api/cards/modifications
    public function modifications(Request $request): JsonResponse
    {
        $user  = $request->user();
        $query = CardModification::with(['card.branch','modifiedBy'])->latest('modified_at');
        if ($user->isBranchManager() && $user->branch_id)
            $query->whereHas('card', fn($q) => $q->where('branch_id', $user->branch_id));
        if ($ac = $request->account_number) $query->where('account_number',$ac);
        return response()->json(['success'=>true,'data'=>$query->paginate(50)]);
    }

    // ── Helpers ──
    private function validationRules(bool $required=true): array
    {
        $r = $required ? 'required' : 'sometimes|required';
        return [
            'account_number'=>"{$r}|string|max:30",'month'=>"{$r}|string|max:20",'month_date'=>"{$r}|date",
            'branch_id'=>'nullable|exists:branches,id','account_type_id'=>'nullable|exists:account_types,id',
            'account_status_id'=>'nullable|exists:account_statuses,id','trading_type_id'=>'nullable|exists:trading_types,id',
            'account_kind'=>'nullable|in:new,sub',
            'broker_id'=>'nullable|exists:employees,id','broker_commission'=>'nullable|numeric|min:0',
            'marketer_id'=>'nullable|exists:employees,id','marketer_commission'=>'nullable|numeric|min:0',
            'ext_marketer1_id'=>'nullable|exists:employees,id','ext_commission1'=>'nullable|numeric|min:0',
            'ext_marketer2_id'=>'nullable|exists:employees,id','ext_commission2'=>'nullable|numeric|min:0',
            'has_rebate'=>'nullable|boolean',
            'rebate_amount'=>'nullable|numeric|min:0|max:5',
            'referral_account'=>'nullable|string|max:50',
            'referral_commission'=>'nullable|numeric|min:0',
            'forex_commission'=>'nullable|numeric|min:0','futures_commission'=>'nullable|numeric|min:0',
            'initial_deposit'=>'nullable|numeric|min:0','monthly_deposit'=>'nullable|numeric|min:0',
            'notes'=>'nullable|string|max:2000',
        ];
    }

    private function extractFields(Request $request): array
    {
        return $request->only(['account_number','month','month_date','branch_id',
            'account_type_id','account_status_id','trading_type_id','account_kind',
            'broker_id','broker_commission','marketer_id','marketer_commission',
            'ext_marketer1_id','ext_commission1','ext_marketer2_id','ext_commission2',
            'forex_commission','futures_commission','initial_deposit','monthly_deposit','notes']);
    }

    private function buildSummary($user): array
    {
        $q = CommissionCard::query();
        if ($user->isBranchManager() && $user->branch_id) $q->forBranch($user->branch_id);
        $d = $q->get(['initial_deposit','monthly_deposit','status']);
        return ['total'=>$d->count(),'initial_deposit'=>round($d->sum('initial_deposit'),2),
            'monthly_deposit'=>round($d->sum('monthly_deposit'),2),
            'modified'=>$d->where('status','modified')->count(),'new_added'=>$d->where('status','new_added')->count()];
    }

    private function groupByBroker($cards): array {
        return $cards->groupBy(fn($c)=>$c->broker?->name??'Unknown')
            ->map(fn($g,$k)=>['group_key'=>$k,'group_icon'=>'🧑‍💼','count'=>$g->count(),
                'initial_deposit'=>round($g->sum('initial_deposit'),2),'monthly_deposit'=>round($g->sum('monthly_deposit'),2),
                'total_comm'=>round($g->sum(fn($c)=>$c->broker_commission+$c->marketer_commission+$c->ext_commission1+$c->ext_commission2),2),
                'modified_count'=>$g->where('status','modified')->count(),'cards'=>$g->values()])->values()->toArray();
    }
    private function groupByBranch($cards): array {
        return $cards->groupBy(fn($c)=>$c->branch?->name_ar??'غير محدد')
            ->map(fn($g,$k)=>['group_key'=>$k,'group_icon'=>'🏢','count'=>$g->count(),
                'initial_deposit'=>round($g->sum('initial_deposit'),2),'monthly_deposit'=>round($g->sum('monthly_deposit'),2),
                'total_comm'=>round($g->sum(fn($c)=>$c->broker_commission+$c->marketer_commission+$c->ext_commission1+$c->ext_commission2),2),
                'modified_count'=>$g->where('status','modified')->count(),'cards'=>$g->values()])->values()->toArray();
    }
    private function groupByMonth($cards): array {
        return $cards->groupBy('month')
            ->map(fn($g,$k)=>['group_key'=>$k,'group_icon'=>'📅','count'=>$g->count(),
                'initial_deposit'=>round($g->sum('initial_deposit'),2),'monthly_deposit'=>round($g->sum('monthly_deposit'),2),
                'total_comm'=>round($g->sum(fn($c)=>$c->broker_commission+$c->marketer_commission+$c->ext_commission1+$c->ext_commission2),2),
                'modified_count'=>$g->where('status','modified')->count(),'cards'=>$g->values()])->values()->toArray();
    }
    private function groupByExtMarketer($cards): array {
        return $cards->groupBy(fn($c)=>$c->extMarketer1?->name??$c->extMarketer2?->name??'بدون مسوّق خارجي')
            ->map(fn($g,$k)=>['group_key'=>$k,'group_icon'=>'🌐','count'=>$g->count(),
                'initial_deposit'=>round($g->sum('initial_deposit'),2),'monthly_deposit'=>round($g->sum('monthly_deposit'),2),
                'total_ext_comm'=>round($g->sum(fn($c)=>$c->ext_commission1+$c->ext_commission2),2),
                'modified_count'=>$g->where('status','modified')->count(),'cards'=>$g->values()])->values()->toArray();
    }
}

    // ── Cache helpers ─────────────────────────────────────────
    private function cacheKey(Request $request, string $prefix): string
    {
        $user = $request->user();
        return $prefix . ':' . ($user?->branch_id ?? 'all') . ':' . md5(json_encode($request->query()));
    }
