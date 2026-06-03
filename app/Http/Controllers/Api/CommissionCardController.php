<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\{CommissionCard, CardModification, ActivityLog};
use Illuminate\Http\{Request, JsonResponse};
use Illuminate\Support\Facades\{DB, Validator, Cache};
use Carbon\Carbon;

class CommissionCardController extends Controller
{
    private array $with = [
        'branch','accountType','accountStatus','tradingType',
        'broker','marketer','extMarketer1','extMarketer2',
        'modifications.modifiedBy','createdBy',
    ];

    // ── BRANCH ISOLATION: hard-lock branch-scoped users to their own branch ──
    private function applyBranchScope($query, Request $request)
    {
        $user = $request->user();
        if ($user->isScopedToBranch()) {
            // HARD LOCK for branch_manager AND viewer — cannot be bypassed by request params
            $query->forBranch($user->branch_id);
        } elseif ($user->isFinanceAdmin() && $request->branch_id) {
            $query->forBranch((int)$request->branch_id);
        }
        return $query;
    }

    // ── Verify branch-scoped user owns a specific card ──
    private function assertOwnership(CommissionCard $card, Request $request): void
    {
        $user = $request->user();
        if ($user->isScopedToBranch() && (int)$card->branch_id !== (int)$user->branch_id) {
            abort(403, 'Access denied: this card does not belong to your branch.');
        }
    }

    // GET /api/cards
    public function index(Request $request): JsonResponse
    {
        // Fast slim path (?fast=1). Folded into the existing route so it works
        // even when routes are cached on the server.
        if ($request->boolean('fast')) return $this->fastList($request);

        $query = CommissionCard::query();
        $this->applyBranchScope($query, $request);

        if ($m  = $request->month)        $query->forMonth($m);
        if ($br = $request->broker_id)    $query->forBroker((int)$br);
        if ($s  = $request->status)       $query->where('status', $s);
        if ($k  = $request->kind)         $query->where('account_kind', $k);
        if ($q  = $request->search)       $query->search($q);
        if ($request->modified_only)      $query->modified();
        if ($min = $request->min_deposit) $query->where('initial_deposit', '>=', (float)$min);

        // Snapshot for summary aggregates (no eager loads, no pagination)
        $summaryQuery = clone $query;

        // Cap raised to 10000 so the cards page can pull the full branch-scoped
        // set in one fast request (client paginates/sorts/totals in the browser).
        $perPage = min((int)($request->per_page ?? 50), 10000);
        $cards   = (clone $query)->with($this->with)
                                 ->orderBy('month_date', 'desc')
                                 ->orderBy('account_number')
                                 ->paginate($perPage);

        return response()->json(['success'=>true,'data'=>$cards,'summary'=>$this->buildSummary($summaryQuery)]);
    }

    // ── GET /api/cards/fast ───────────────────────────────────
    // High-speed slim list for the cards table: raw JOIN (no Eloquent
    // hydration of 10 relations), only the columns the table needs, plus a
    // short version-keyed cache. Dramatically faster than index() for thousands
    // of rows. Returns flat rows + pre-computed totals.
    public function fastList(Request $request): JsonResponse
    {
        try {
        $user = $request->user();

        // ── Base filtered query (no joins) — reused for aggregates + page ──
        $base = DB::table('commission_cards as c')->whereNull('c.deleted_at');
        if ($user->isScopedToBranch())                          $base->where('c.branch_id', $user->branch_id);
        elseif ($user->isFinanceAdmin() && $request->branch_id) $base->where('c.branch_id', (int)$request->branch_id);
        if ($m = $request->month)     $base->where('c.month', $m);
        if ($s = $request->status)    $base->where('c.status', $s);
        if ($k = $request->kind)      $base->where('c.account_kind', $k);
        if ($b = $request->broker_id) $base->where('c.broker_id', (int)$b);
        if (($src = $request->source) === 'cc')       $base->whereNotNull('c.cc_branch_id');
        elseif ($src === 'regular')                   $base->whereNull('c.cc_branch_id');
        if ($q = trim((string)$request->q)) {
            $like = "%{$q}%";
            $base->where(function ($w) use ($like) {
                $w->where('c.account_number', 'like', $like)
                  ->orWhere('c.month', 'like', $like)
                  ->orWhereExists(function ($s) use ($like) {
                      $s->select(DB::raw(1))->from('employees as se')
                        ->whereColumn('se.id', 'c.broker_id')->where('se.name', 'like', $like);
                  })
                  ->orWhereExists(function ($s) use ($like) {
                      $s->select(DB::raw(1))->from('employees as sm')
                        ->whereColumn('sm.id', 'c.marketer_id')->where('sm.name', 'like', $like);
                  });
            });
        }

        // ── Aggregates over the WHOLE filtered set (one fast SQL row) ──
        $agg = (clone $base)->selectRaw(
            'COUNT(*) cnt, '.
            'COALESCE(SUM(initial_deposit),0) dep, COALESCE(SUM(monthly_deposit),0) mon, '.
            'COALESCE(SUM(broker_commission),0) bc, COALESCE(SUM(marketer_commission),0) mc, '.
            'COALESCE(SUM(ext_commission1),0)+COALESCE(SUM(ext_commission2),0) ec, '.
            "SUM(CASE WHEN status='modified'  THEN 1 ELSE 0 END) modc, ".
            'SUM(CASE WHEN cc_branch_id IS NOT NULL THEN 1 ELSE 0 END) ccc, '.
            "SUM(CASE WHEN account_kind='new' THEN 1 ELSE 0 END) newc, ".
            "SUM(CASE WHEN account_kind='sub' THEN 1 ELSE 0 END) subc"
        )->first();
        $total = (int) ($agg->cnt ?? 0);

        // ── Top broker & branch (for insights) — small GROUP BY ──
        $topBroker = (clone $base)->leftJoin('employees as e','c.broker_id','=','e.id')
            ->whereNotNull('c.broker_id')->groupBy('e.name')
            ->selectRaw('e.name nm, COUNT(*) ct, COALESCE(SUM(c.broker_commission),0) comm')
            ->orderByDesc('ct')->limit(1)->first();
        $topBranch = (clone $base)->leftJoin('branches as br2','c.branch_id','=','br2.id')
            ->groupBy('br2.name_ar')->selectRaw('br2.name_ar nm, COUNT(*) ct')
            ->orderByDesc('ct')->limit(1)->first();

        // ── Sorting ──
        $sortMap = [
            'account_number'=>'c.account_number','month'=>'c.month_date',
            'broker_commission'=>'c.broker_commission','marketer_commission'=>'c.marketer_commission',
            'initial_deposit'=>'c.initial_deposit','monthly_deposit'=>'c.monthly_deposit',
            'status'=>'c.status','account_kind'=>'c.account_kind',
        ];
        $sort = $sortMap[$request->sort] ?? 'c.month_date';
        $dir  = strtolower((string)$request->dir) === 'asc' ? 'asc' : 'desc';

        // ── Page of rows (with names via join) ──
        $page = max(1, (int)($request->page ?? 1));
        $per  = min(max((int)($request->per ?? 100), 10), 500);

        // Filter dropdown options — only on the first page (cheap, sent once).
        $months = []; $brokers = [];
        if ($page <= 1) {
            $mScope = DB::table('commission_cards as cm')->whereNull('cm.deleted_at');
            if ($user->isScopedToBranch())                          $mScope->where('cm.branch_id', $user->branch_id);
            elseif ($user->isFinanceAdmin() && $request->branch_id) $mScope->where('cm.branch_id', (int)$request->branch_id);
            $months = $mScope->select('cm.month')->selectRaw('MAX(cm.month_date) md')
                ->groupBy('cm.month')->orderByDesc('md')->pluck('cm.month');
            $brokers = DB::table('employees')->orderBy('name')->get(['id','name'])
                ->map(fn($e) => ['id'=>$e->id,'name'=>$e->name]);
        }

        $rows = (clone $base)
            ->leftJoin('branches as br','c.branch_id','=','br.id')
            ->leftJoin('employees as bk','c.broker_id','=','bk.id')
            ->leftJoin('employees as mk','c.marketer_id','=','mk.id')
            ->orderBy($sort, $dir)->orderBy('c.account_number')
            ->forPage($page, $per)
            ->get([
                'c.id','c.account_number','c.month','c.account_kind','c.status',
                'c.broker_commission','c.marketer_commission','c.ext_commission1','c.ext_commission2',
                'c.initial_deposit','c.monthly_deposit','c.cc_branch_id',
                'br.name_ar as branch_ar','br.name_en as branch_en',
                'bk.name as broker_name','mk.name as marketer_name',
            ]);

        return response()->json([
            'success' => true,
            'rows'    => $rows,
            'total'   => $total,
            'page'    => $page,
            'per'     => $per,
            'totals'  => [
                'dep'=>round((float)$agg->dep,2),'mon'=>round((float)$agg->mon,2),
                'bc'=>round((float)$agg->bc,2),'mc'=>round((float)$agg->mc,2),'ec'=>round((float)$agg->ec,2),
                'mod'=>(int)$agg->modc,'cc'=>(int)$agg->ccc,'new'=>(int)$agg->newc,'sub'=>(int)$agg->subc,
            ],
            'top_broker' => $topBroker ? ['name'=>$topBroker->nm,'count'=>(int)$topBroker->ct,'comm'=>round((float)$topBroker->comm,2)] : null,
            'top_branch' => $topBranch ? ['name'=>$topBranch->nm,'count'=>(int)$topBranch->ct] : null,
            'months'  => $months,
            'brokers' => $brokers,
            'scope'   => $user->isScopedToBranch() ? ($user->branch?->name_ar ?? 'فرعك') : 'جميع الفروع',
        ]);
        } catch (\Throwable $e) {
            \Log::error('fastList failed: '.$e->getMessage().' @ '.basename($e->getFile()).':'.$e->getLine());
            return response()->json(['success'=>false,'message'=>'تعذّر تحميل البيانات، حاول مرة أخرى.'], 500);
        }
    }

    // ── GET /api/cards/summary ────────────────────────────────
    // Instant dashboard/report data read straight from the pre-aggregated
    // card_summaries table (one row per branch+month). Never scans the big
    // commission_cards table — so it stays instant even with millions of cards.
    public function summary(Request $request): JsonResponse
    {
        $user = $request->user();

        $base = DB::table('card_summaries as s');
        if ($user->isScopedToBranch())                          $base->where('s.branch_id', $user->branch_id);
        elseif ($user->isFinanceAdmin() && $request->branch_id) $base->where('s.branch_id', (int)$request->branch_id);
        if ($from = $request->month_from) { try { $base->whereDate('s.month_date','>=',Carbon::parse("01 {$from}")); } catch (\Exception $e) {} }
        if ($to   = $request->month_to)   { try { $base->whereDate('s.month_date','<=',Carbon::parse("01 {$to}")->endOfMonth()); } catch (\Exception $e) {} }

        // overall totals
        $tot = (clone $base)->selectRaw('
            COALESCE(SUM(total_count),0) cnt, COALESCE(SUM(unique_accounts),0) uniq,
            COALESCE(SUM(new_count),0) newc, COALESCE(SUM(sub_count),0) subc,
            COALESCE(SUM(modified_count),0) modc, COALESCE(SUM(cc_count),0) ccc,
            COALESCE(SUM(no_deposit_count),0) ndc,
            COALESCE(SUM(total_initial),0) dep, COALESCE(SUM(total_monthly),0) mon,
            COALESCE(SUM(total_broker_comm),0) bc, COALESCE(SUM(total_marketer_comm),0) mc,
            COALESCE(SUM(total_ext_comm),0) ec
        ')->first();

        // per-month rollup (for trend chart)
        $byMonth = (clone $base)->selectRaw('
            month, MAX(month_date) md,
            SUM(total_count) cnt, SUM(unique_accounts) uniq,
            SUM(total_initial) dep, SUM(total_monthly) mon
        ')->groupBy('month')->orderByRaw('MAX(month_date)')->get();

        // per-branch rollup (for branch comparison + modifications + empty-account detection)
        $byBranch = (clone $base)->leftJoin('branches as br','s.branch_id','=','br.id')
            ->selectRaw("COALESCE(br.name_ar,'غير محدد') nar, COALESCE(br.name_en,'Unassigned') nen, SUM(total_count) cnt, SUM(total_initial) dep, SUM(modified_count) modc, SUM(new_count) newc, SUM(no_deposit_count) ndc")
            ->groupBy('br.name_ar','br.name_en')->orderByDesc('cnt')->get();

        // per-branch per-month rollup (for stacked monthly chart)
        $byBranchMonth = (clone $base)->leftJoin('branches as br2','s.branch_id','=','br2.id')
            ->selectRaw("COALESCE(br2.name_ar,'غير محدد') nar, COALESCE(br2.name_en,'Unassigned') nen, s.month, MAX(s.month_date) md, SUM(s.new_count) newc, SUM(s.total_count) cnt")
            ->groupBy('s.branch_id','br2.name_ar','br2.name_en','s.month')
            ->orderByRaw('MAX(s.month_date)')->get();

        // Fallback live CC count if summaries are stale/empty
        $liveCc = (int)$tot->ccc;
        if ($liveCc === 0) {
            try {
                $ccQ = DB::table('commission_cards')->whereNull('deleted_at')->whereNotNull('cc_branch_id');
                if ($user->isScopedToBranch()) $ccQ->where('branch_id', $user->branch_id);
                $liveCc = (int)$ccQ->count();
            } catch (\Throwable $e) {}
        }

        // ── Leaderboards (best broker / best marketer by NEW accounts) ──
        // Queried straight from commission_cards (indexed GROUP BY) — small + fast.
        $topBrokers = collect(); $topMarketers = collect();
        try {
            $cc = DB::table('commission_cards as c')->whereNull('c.deleted_at');
            if ($user->isScopedToBranch())                          $cc->where('c.branch_id', $user->branch_id);
            elseif ($user->isFinanceAdmin() && $request->branch_id) $cc->where('c.branch_id', (int)$request->branch_id);
            if ($from = $request->month_from) { try { $cc->whereDate('c.month_date','>=',Carbon::parse("01 {$from}")); } catch (\Exception $e) {} }
            if ($to   = $request->month_to)   { try { $cc->whereDate('c.month_date','<=',Carbon::parse("01 {$to}")->endOfMonth()); } catch (\Exception $e) {} }

            $topBrokers = (clone $cc)->leftJoin('employees as e','c.broker_id','=','e.id')
                ->whereNotNull('c.broker_id')
                ->selectRaw("e.name nm, COUNT(*) total, SUM(CASE WHEN c.account_kind='new' THEN 1 ELSE 0 END) newc, COALESCE(SUM(c.broker_commission),0) comm")
                ->groupBy('e.name')->orderByDesc('newc')->orderByDesc('total')->limit(3)->get();

            $topMarketers = (clone $cc)->leftJoin('employees as m','c.marketer_id','=','m.id')
                ->whereNotNull('c.marketer_id')
                ->selectRaw("m.name nm, COUNT(*) total, SUM(CASE WHEN c.account_kind='new' THEN 1 ELSE 0 END) newc, COALESCE(SUM(c.marketer_commission),0) comm")
                ->groupBy('m.name')->orderByDesc('newc')->orderByDesc('total')->limit(3)->get();
        } catch (\Throwable $e) {}

        return response()->json([
            'success'  => true,
            'totals'   => [
                'count'=>(int)$tot->cnt,'unique'=>(int)$tot->uniq,
                'new'=>(int)$tot->newc,'sub'=>(int)$tot->subc,'mod'=>(int)$tot->modc,'cc'=>$liveCc,
                'no_dep'=>(int)$tot->ndc,
                'dep'=>round((float)$tot->dep,2),'mon'=>round((float)$tot->mon,2),
                'bc'=>round((float)$tot->bc,2),'mc'=>round((float)$tot->mc,2),'ec'=>round((float)$tot->ec,2),
            ],
            'by_month'       => $byMonth,
            'by_branch'      => $byBranch,
            'by_branch_month'=> $byBranchMonth,
            'top_brokers'    => $topBrokers,
            'top_marketers'  => $topMarketers,
            'scope'          => $user->isScopedToBranch() ? ($user->branch?->name_ar ?? 'فرعك') : 'جميع الفروع',
        ]);
    }

    // Invalidate every cached fast-list (called on any card write)
    private function bumpCardsCache(): void
    {
        try {
            $v = (int) Cache::get('cards_cache_ver', 1);
            Cache::forever('cards_cache_ver', $v + 1);
        } catch (\Throwable $e) { /* cache unavailable — ignore */ }
    }

    // Rebuild card_summaries for a specific branch synchronously (fast, ~1 query)
    public static function refreshBranchSummary(?int $branchId): void
    {
        if (!$branchId) return;
        try {
            $row = DB::table('commission_cards')
                ->whereNull('deleted_at')
                ->where('branch_id', $branchId)
                ->selectRaw('
                    branch_id,
                    month,
                    MAX(month_date) AS month_date,
                    COUNT(*) AS total_count,
                    COUNT(DISTINCT account_number) AS unique_accounts,
                    SUM(CASE WHEN account_kind="new"  THEN 1 ELSE 0 END) AS new_count,
                    SUM(CASE WHEN account_kind="sub"  THEN 1 ELSE 0 END) AS sub_count,
                    SUM(CASE WHEN status="modified"   THEN 1 ELSE 0 END) AS modified_count,
                    SUM(CASE WHEN status="new_added"  THEN 1 ELSE 0 END) AS new_added_count,
                    SUM(CASE WHEN cc_branch_id IS NOT NULL THEN 1 ELSE 0 END) AS cc_count,
                    SUM(CASE WHEN initial_deposit = 0 OR initial_deposit IS NULL THEN 1 ELSE 0 END) AS no_deposit_count,
                    COALESCE(SUM(initial_deposit),0) AS total_initial,
                    COALESCE(SUM(monthly_deposit),0) AS total_monthly,
                    COALESCE(SUM(broker_commission),0) AS total_broker_comm,
                    COALESCE(SUM(marketer_commission),0) AS total_marketer_comm,
                    COALESCE(SUM(ext_commission1),0)+COALESCE(SUM(ext_commission2),0) AS total_ext_comm
                ')
                ->groupBy('branch_id', 'month')
                ->get();

            DB::table('card_summaries')->where('branch_id', $branchId)->delete();
            $now = now();
            $payload = $row->map(fn($r) => [
                'branch_id'           => $r->branch_id,
                'month'               => $r->month,
                'month_date'          => $r->month_date,
                'total_count'         => (int) $r->total_count,
                'unique_accounts'     => (int) $r->unique_accounts,
                'new_count'           => (int) $r->new_count,
                'sub_count'           => (int) $r->sub_count,
                'modified_count'      => (int) $r->modified_count,
                'new_added_count'     => (int) $r->new_added_count,
                'cc_count'            => (int) $r->cc_count,
                'no_deposit_count'    => (int) $r->no_deposit_count,
                'total_initial'       => $r->total_initial,
                'total_monthly'       => $r->total_monthly,
                'total_broker_comm'   => $r->total_broker_comm,
                'total_marketer_comm' => $r->total_marketer_comm,
                'total_ext_comm'      => $r->total_ext_comm,
                'rebuilt_at'          => $now,
                'updated_at'          => $now,
                'created_at'          => $now,
            ])->toArray();

            if ($payload) {
                foreach (array_chunk($payload, 200) as $chunk) {
                    DB::table('card_summaries')->insert($chunk);
                }
            }
            try { Cache::forever('cards_cache_ver', (int) Cache::get('cards_cache_ver', 1) + 1); } catch (\Throwable $e) {}
        } catch (\Throwable $e) { /* non-blocking */ }
    }

    // GET /api/cards/{id}
    public function show(Request $request, int $id): JsonResponse
    {
        $card = CommissionCard::with($this->with)->findOrFail($id);
        $this->assertOwnership($card, $request);
        return response()->json(['success'=>true,'data'=>$card]);
    }

    // POST /api/cards
    public function store(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), $this->validationRules());
        if ($v->fails()) return response()->json(['success'=>false,'errors'=>$v->errors()],422);

        $user = $request->user();

        // Branch-scoped users (branch_manager + viewer) are FORCED to their own branch.
        // Guard against null branch_id (shouldn't happen but prevents data leak).
        if ($user->isScopedToBranch() && !$user->branch_id) {
            return response()->json(['success'=>false,'message'=>'Account has no branch assigned. Contact Finance Admin.'],403);
        }
        $branchId = $user->isScopedToBranch()
            ? $user->branch_id
            : ($request->branch_id ? (int)$request->branch_id : null);

        // Check for an active (non-deleted) duplicate
        if (CommissionCard::where('account_number', $request->account_number)
                          ->where('month', $request->month)
                          ->exists()) {
            return response()->json(['success'=>false,'message'=>"Account #{$request->account_number} already exists for {$request->month}."],409);
        }

        $card = DB::transaction(function() use ($request, $branchId, $user) {
            $fields               = $this->extractFields($request);
            $fields['branch_id']  = $branchId;
            $fields['status']     = 'new_added';
            $fields['created_by'] = $user->id;

            // If a soft-deleted record exists for the same ac+month, restore it instead
            // of inserting — avoids the DB unique constraint (uq_ac_month) crash.
            $trashed = CommissionCard::withTrashed()
                ->where('account_number', $request->account_number)
                ->where('month', $request->month)
                ->first();

            if ($trashed) {
                $trashed->restore();
                $trashed->update($fields);
                $card = $trashed->fresh();
            } else {
                $card = CommissionCard::create($fields);
            }

            ActivityLog::record('create_card', $card, ['account'=>$card->account_number,'branch_id'=>$branchId]);
            return $card;
        });

        $this->bumpCardsCache();
        self::refreshBranchSummary($card->branch_id);
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

        $this->bumpCardsCache();
        self::refreshBranchSummary($card->branch_id);
        return response()->json(['success'=>true,'message'=>"Card #{$card->account_number} updated.",'data'=>$card->fresh($this->with)]);
    }

    // DELETE /api/cards/{id}  — Finance Admin only
    public function destroy(Request $request, int $id): JsonResponse
    {
        if (!$request->user()->isFinanceAdmin())
            return response()->json(['success'=>false,'message'=>'Finance Admin only.'],403);

        $card = CommissionCard::findOrFail($id);
        $branchId = $card->branch_id;
        $card->update(['status'=>'inactive']);
        $card->delete();
        ActivityLog::record('delete_card', $card);
        $this->bumpCardsCache();
        self::refreshBranchSummary($branchId);
        return response()->json(['success'=>true,'message'=>"Card #{$card->account_number} deleted."]);
    }

    // GET /api/cards/tree
    public function tree(Request $request): JsonResponse
    {
        $user  = $request->user();
        // Slim JOIN query (no Eloquent hydration / no N relation queries) mapped to the
        // SAME nested shape the grouping helpers expect — keeps the tree fast at scale.
        $base = DB::table('commission_cards as c')->whereNull('c.deleted_at');
        if ($user->isScopedToBranch())                          $base->where('c.branch_id', $user->branch_id);
        elseif ($user->isFinanceAdmin() && $request->branch_id) $base->where('c.branch_id', (int)$request->branch_id);
        if ($m = $request->month) $base->where('c.month', $m);

        $cards = $base
            ->leftJoin('branches as br','c.branch_id','=','br.id')
            ->leftJoin('employees as bk','c.broker_id','=','bk.id')
            ->leftJoin('employees as mk','c.marketer_id','=','mk.id')
            ->leftJoin('employees as e1','c.ext_marketer1_id','=','e1.id')
            ->leftJoin('employees as e2','c.ext_marketer2_id','=','e2.id')
            ->orderByDesc('c.month_date')->orderBy('c.account_number')
            ->get([
                'c.id','c.account_number','c.month','c.month_date','c.account_kind','c.status',
                'c.broker_commission','c.marketer_commission','c.ext_commission1','c.ext_commission2',
                'c.initial_deposit','c.monthly_deposit',
                'br.name_ar as branch_ar','br.name_en as branch_en',
                'bk.name as broker_name','mk.name as marketer_name','e1.name as e1_name','e2.name as e2_name',
            ])
            ->map(fn($c) => (object)[
                'id'=>$c->id,'account_number'=>$c->account_number,'month'=>$c->month,'month_date'=>$c->month_date,
                'account_kind'=>$c->account_kind,'status'=>$c->status,
                'initial_deposit'=>(float)$c->initial_deposit,'monthly_deposit'=>(float)$c->monthly_deposit,
                'broker_commission'=>(float)$c->broker_commission,'marketer_commission'=>(float)$c->marketer_commission,
                'ext_commission1'=>(float)$c->ext_commission1,'ext_commission2'=>(float)$c->ext_commission2,
                'broker'=>$c->broker_name ? (object)['name'=>$c->broker_name] : null,
                'marketer'=>$c->marketer_name ? (object)['name'=>$c->marketer_name] : null,
                'extMarketer1'=>$c->e1_name ? (object)['name'=>$c->e1_name] : null,
                'extMarketer2'=>$c->e2_name ? (object)['name'=>$c->e2_name] : null,
                'branch'=>(object)['name_ar'=>$c->branch_ar,'name_en'=>$c->branch_en],
            ]);

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
                'total_all_comm'    => round($cards->sum(fn($c) => $c->broker_commission+$c->marketer_commission+$c->ext_commission1+$c->ext_commission2),2),
                'modified_count'    => $cards->where('status','modified')->count(),
            ],
            'tree' => $tree,
        ]);
    }

    // GET /api/cards/report
    public function report(Request $request): JsonResponse
    {
        $user = $request->user();

        $build = function () use ($request, $user) {
            // ── Slim filtered base (no Eloquent hydration) ──
            $base = DB::table('commission_cards as c')->whereNull('c.deleted_at');
            if ($user->isScopedToBranch())                          $base->where('c.branch_id', $user->branch_id);
            elseif ($user->isFinanceAdmin() && $request->branch_id) $base->where('c.branch_id', (int)$request->branch_id);
            if ($from = $request->month_from) { try { $base->whereDate('c.month_date','>=',Carbon::parse("01 {$from}")); } catch(\Exception $e){} }
            if ($to   = $request->month_to)   { try { $base->whereDate('c.month_date','<=',Carbon::parse("01 {$to}")->endOfMonth()); } catch(\Exception $e){} }
            if ($br   = $request->broker_id)   $base->where('c.broker_id', (int)$br);
            if ($s    = $request->status)      $base->where('c.status', $s);
            if ($k    = $request->kind)        $base->where('c.account_kind', $k);
            if ($min  = $request->min_deposit) $base->where('c.initial_deposit', '>=', (float)$min);
            if ($brN  = $request->broker_name) {
                $base->whereExists(fn($q) => $q->select(DB::raw(1))->from('employees as eb')
                    ->whereColumn('eb.id','c.broker_id')->where('eb.name','like',"%{$brN}%"));
            }
            if ($q = trim((string)$request->search)) {
                $base->where(fn($w) => $w->where('c.account_number','like',"%{$q}%")->orWhere('c.month','like',"%{$q}%"));
            }

            // ── ONE consolidated aggregate query (was 10 separate queries) ──
            $agg = (clone $base)->selectRaw('
                COUNT(*) cnt, COUNT(DISTINCT account_number) uniq,
                COALESCE(SUM(initial_deposit),0) dep, COALESCE(SUM(monthly_deposit),0) mon,
                COALESCE(SUM(broker_commission),0) bc, COALESCE(SUM(marketer_commission),0) mc,
                COALESCE(SUM(ext_commission1),0) ec1, COALESCE(SUM(ext_commission2),0) ec2,
                SUM(CASE WHEN status = "modified"  THEN 1 ELSE 0 END) modc,
                SUM(CASE WHEN status = "new_added" THEN 1 ELSE 0 END) newc
            ')->first();
            $exactCount = (int)($agg->cnt ?? 0);

            // ── Slim page of rows via JOIN (no model hydration, no N relation queries) ──
            $limit = min((int)($request->per_page ?? 5000), 10000);
            $rows = (clone $base)
                ->leftJoin('branches as br','c.branch_id','=','br.id')
                ->leftJoin('employees as bk','c.broker_id','=','bk.id')
                ->leftJoin('employees as mk','c.marketer_id','=','mk.id')
                ->orderByDesc('c.month_date')->orderBy('c.account_number')->limit($limit)
                ->get([
                    'c.id','c.account_number','c.month','c.month_date','c.account_kind','c.status','c.cc_status','c.cc_branch_id',
                    'c.broker_commission','c.marketer_commission','c.ext_commission1','c.ext_commission2',
                    'c.initial_deposit','c.monthly_deposit',
                    'br.name_ar as branch_ar','br.name_en as branch_en','bk.name as broker_name','mk.name as marketer_name',
                ]);
            // Map to the SAME nested shape the frontend expects (so nothing else changes)
            $data = $rows->map(fn($c) => [
                'id'=>$c->id,'account_number'=>$c->account_number,'month'=>$c->month,'month_date'=>$c->month_date,
                'account_kind'=>$c->account_kind,'status'=>$c->status,'cc_status'=>$c->cc_status,'cc_branch_id'=>$c->cc_branch_id,
                'broker_commission'=>$c->broker_commission,'marketer_commission'=>$c->marketer_commission,
                'ext_commission1'=>$c->ext_commission1,'ext_commission2'=>$c->ext_commission2,
                'initial_deposit'=>$c->initial_deposit,'monthly_deposit'=>$c->monthly_deposit,
                'branch'=>['name_ar'=>$c->branch_ar,'name_en'=>$c->branch_en],
                'broker'=>$c->broker_name ? ['name'=>$c->broker_name] : null,
                'marketer'=>$c->marketer_name ? ['name'=>$c->marketer_name] : null,
            ]);

            return [
                'success'         => true,
                'count'           => $exactCount,
                'unique_accounts' => (int)($agg->uniq ?? 0),
                'records_limited' => $exactCount > $limit,
                'branch_scope'    => $user->isScopedToBranch() ? ($user->branch?->name_ar ?? 'فرعك') : 'جميع الفروع',
                'summary' => [
                    'total_initial_deposit' => round((float)$agg->dep, 2),
                    'total_monthly_deposit' => round((float)$agg->mon, 2),
                    'total_broker_comm'     => round((float)$agg->bc, 2),
                    'total_marketer_comm'   => round((float)$agg->mc, 2),
                    'total_ext1_comm'       => round((float)$agg->ec1, 2),
                    'total_ext2_comm'       => round((float)$agg->ec2, 2),
                    'modified_count'        => (int)$agg->modc,
                    'new_added_count'       => (int)$agg->newc,
                ],
                'data' => $data,
            ];
        };

        // Application cache (item 4): version-keyed, auto-invalidated on any write.
        // First read pays the cost; every repeat read is instant — across all
        // pages that use this endpoint (cards, reports).
        try {
            $scope = $user->isScopedToBranch() ? 'b'.$user->branch_id
                   : (($user->isFinanceAdmin() && $request->branch_id) ? 'fb'.(int)$request->branch_id : 'all');
            $ver = Cache::get('cards_cache_ver', 1);
            $key = 'cards_report_v'.$ver.'_'.$scope.'_'.md5(json_encode([
                $request->month_from, $request->month_to, $request->broker_id, $request->broker_name,
                $request->status, $request->kind, $request->min_deposit, $request->search, $request->per_page,
            ]));
            $payload = Cache::remember($key, 120, $build);
        } catch (\Throwable $e) {
            try { $payload = $build(); }
            catch (\Throwable $e2) {
                \Log::error('report failed: '.$e2->getMessage().' @ '.basename($e2->getFile()).':'.$e2->getLine());
                return response()->json(['success'=>false,'message'=>'تعذّر تحميل التقرير، حاول مرة أخرى.'], 500);
            }
        }

        return response()->json($payload);
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
            'account_number'=>"{$r}|string|max:30",'month'=>"{$r}|string|max:20",'month_date'=>'nullable|date',
            'branch_id'=>'nullable|exists:branches,id','account_type_id'=>'nullable|exists:account_types,id',
            'account_status_id'=>'nullable|exists:account_statuses,id','trading_type_id'=>'nullable|exists:trading_types,id',
            'account_kind'=>'nullable|in:new,sub',
            'broker_id'=>'nullable|exists:employees,id','broker_commission'=>'nullable|numeric|min:0',
            'marketer_id'=>'nullable|exists:employees,id','marketer_commission'=>'nullable|numeric|min:0',
            'ext_marketer1_id'=>'nullable|exists:employees,id','ext_commission1'=>'nullable|numeric|min:0',
            'ext_marketer2_id'=>'nullable|exists:employees,id','ext_commission2'=>'nullable|numeric|min:0',
            'forex_commission'=>'nullable|numeric|min:0','futures_commission'=>'nullable|numeric|min:0',
            'initial_deposit'=>'nullable|numeric|min:0','monthly_deposit'=>'nullable|numeric|min:0',
            'notes'=>'nullable|string|max:2000',
        ];
    }

    private function extractFields(Request $request): array
    {
        $fields = $request->only([
            'account_number','month','month_date','branch_id',
            'account_type_id','account_status_id','trading_type_id','account_kind',
            'broker_id','broker_commission','marketer_id','marketer_commission',
            'ext_marketer1_id','ext_commission1','ext_marketer2_id','ext_commission2',
            'forex_commission','futures_commission','initial_deposit','monthly_deposit','notes',
        ]);

        // Auto-derive month_date from month if not supplied (e.g. "Jan 2025" → "2025-01-01")
        if (empty($fields['month_date']) && !empty($fields['month'])) {
            try {
                $fields['month_date'] = \Carbon\Carbon::parse('01 ' . $fields['month'])->format('Y-m-d');
            } catch (\Exception $e) {
                $fields['month_date'] = now()->startOfMonth()->format('Y-m-d');
            }
        }

        return $fields;
    }

    private function buildSummary($query): array
    {
        $q = clone $query;
        return [
            'total'           => (clone $q)->count(),
            'initial_deposit' => round((clone $q)->sum('initial_deposit'), 2),
            'monthly_deposit' => round((clone $q)->sum('monthly_deposit'), 2),
            'modified'        => (clone $q)->where('status', 'modified')->count(),
            'new_added'       => (clone $q)->where('status', 'new_added')->count(),
        ];
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
