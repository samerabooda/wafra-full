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
            // HARD LOCK for branch_manager AND viewer — cannot be bypassed by request params.
            // branchScopeId() is -1 for a branch-less user → they see nothing (never everything).
            $query->forBranch($user->branchScopeId());
        } elseif ($user->isFinanceAdmin() && $request->branch_id) {
            $query->forBranch((int)$request->branch_id);
        }
        return $query;
    }

    // ── Verify branch-scoped user owns a specific card ──
    private function assertOwnership(CommissionCard $card, Request $request): void
    {
        $user = $request->user();
        if ($user->isScopedToBranch() && (int)$card->branch_id !== $user->branchScopeId()) {
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
        // No upper cap — the full filtered set can be returned in one request.
        $perPage = max((int)($request->per_page ?? 100), 1);
        // LIGHT eager-load for list/search/modified: select only the columns the UI needs.
        // (The default $this->with pulled full User rows — including base64 profile photos —
        //  and full modification old_data/new_data, bloating one card to ~200KB and a small
        //  list to >1MB, which made the browser appear stuck on "loading".)
        $cards   = (clone $query)->with([
                        'branch:id,name_ar,name_en',
                        'accountType:id,name_ar,name_en',
                        'accountStatus:id,name_ar,name_en',
                        'tradingType:id,name_ar,name_en',
                        'broker:id,name', 'marketer:id,name',
                        'extMarketer1:id,name', 'extMarketer2:id,name',
                        'createdBy:id,name',
                        'modifications' => fn($q) => $q
                            ->select('id','card_id','reason','notes','modified_by','modified_at')
                            ->with('modifiedBy:id,name')
                            ->orderByDesc('modified_at'),
                    ])
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
        if ($user->isScopedToBranch())                          $base->where('c.branch_id', $user->branchScopeId());
        elseif ($user->isFinanceAdmin() && $request->branch_id) $base->where('c.branch_id', (int)$request->branch_id);
        if ($m = $request->month)     $base->where('c.month', $m);
        if ($s = $request->status)    $base->where('c.status', $s);
        if ($k = $request->kind)      $base->where('c.account_kind', $k);
        if ($b = $request->broker_id) $base->where('c.broker_id', (int)$b);
        if (($src = $request->source) === 'cc')       $base->whereNotNull('c.cc_branch_id');
        elseif ($src === 'regular')                   $base->whereNull('c.cc_branch_id');
        // Default view = real commission cards only: hide in-flight CC leads (drafts / awaiting /
        // accepted / rejected). They live in the Call-Center screen until the branch completes them.
        else $base->where(fn($w) => $w->whereNull('c.cc_status')->orWhereNotIn('c.cc_status', ['cc_pending','branch_pending','accepted','in_progress','rejected','declined']));
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

        // ── Per-column "Excel AutoFilter" selections (colf = {"col":["rawval",...]}) ──
        // Values are RAW (ids / strings / rounded numbers), with '__blank__' = NULL/empty.
        $this->applyColumnFilters($base, $request->input('colf'));

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

        // ── Sorting (whitelist → safe to interpolate into orderByRaw) ──
        $sortMap = [
            'account_number'=>'c.account_number','month'=>'c.month_date',
            'broker'=>'bk.name','broker_commission'=>'c.broker_commission',
            'marketer'=>'mk.name','marketer_commission'=>'c.marketer_commission',
            'marketer2'=>'m2.name','ext_commission2'=>'c.ext_commission2',
            'referral'=>'c.referral_account','referral_commission'=>'c.referral_commission',
            'total'=>'(c.broker_commission+c.marketer_commission+c.ext_commission1+c.ext_commission2)',
            'initial_deposit'=>'c.initial_deposit','monthly_deposit'=>'c.monthly_deposit',
            'status'=>'c.status','account_kind'=>'c.account_kind',
            'branch'=>'br.name_ar','source'=>'c.cc_branch_id',
        ];
        $sort = $sortMap[$request->sort] ?? 'c.month_date';
        $dir  = strtolower((string)$request->dir) === 'asc' ? 'asc' : 'desc';

        // ── Page of rows (with names via join) ──
        $page = max(1, (int)($request->page ?? 1));
        // No upper cap on the number of cards — load the full set in one request.
        $per  = max((int)($request->per ?? 100000), 10);

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
            ->leftJoin('employees as m2','c.ext_marketer2_id','=','m2.id')
            ->orderByRaw("{$sort} {$dir}")->orderBy('c.account_number')
            ->forPage($page, $per)
            ->get([
                'c.id','c.account_number','c.month','c.month_date','c.modified_month','c.account_kind','c.status',
                'c.broker_commission','c.marketer_commission','c.ext_commission1','c.ext_commission2',
                'c.initial_deposit','c.monthly_deposit','c.cc_branch_id','c.referral_account','c.referral_commission','c.parent_account',
                'br.name_ar as branch_ar','br.name_en as branch_en',
                'bk.name as broker_name','mk.name as marketer_name','m2.name as marketer2_name',
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

    /** SQL expression per filterable column. ROUND() for money so display values match. */
    private const COLF_SQL = [
        'account_number'      => 'c.account_number',
        'broker'              => 'c.broker_id',
        'marketer'            => 'c.marketer_id',
        'marketer2'           => 'c.ext_marketer2_id',
        'referral'            => 'c.referral_account',
        'month'               => 'c.month',
        'account_kind'        => 'c.account_kind',
        'branch'              => 'c.branch_id',
        'broker_commission'   => 'ROUND(c.broker_commission,2)',
        'marketer_commission' => 'ROUND(c.marketer_commission,2)',
        'ext_commission2'     => 'ROUND(c.ext_commission2,2)',
        'referral_commission' => 'ROUND(c.referral_commission,2)',
        'initial_deposit'     => 'ROUND(c.initial_deposit,2)',
    ];
    // Columns whose '__blank__' selection means a NULL id / empty string in the DB.
    private const COLF_NULLABLE = [
        'broker'=>'c.broker_id','marketer'=>'c.marketer_id','marketer2'=>'c.ext_marketer2_id',
        'referral'=>'c.referral_account','month'=>'c.month',
    ];

    /** Apply Excel-style per-column value-set filters to the base query. */
    private function applyColumnFilters($base, $colf): void
    {
        if (is_string($colf)) $colf = json_decode($colf, true);
        if (!is_array($colf) || !$colf) return;

        foreach ($colf as $col => $vals) {
            if (!isset(self::COLF_SQL[$col]) || !is_array($vals) || !count($vals)) continue;
            $expr  = self::COLF_SQL[$col];
            $clean = []; $blank = false;
            foreach ($vals as $v) {
                if ($v === '' || $v === null || $v === '__blank__') $blank = true; else $clean[] = $v;
            }
            $base->where(function ($w) use ($expr, $clean, $blank, $col) {
                if ($clean) $w->whereIn(DB::raw($expr), $clean);
                if ($blank && isset(self::COLF_NULLABLE[$col])) {
                    $idcol = self::COLF_NULLABLE[$col];
                    $w->orWhereNull($idcol);
                    if (in_array($col, ['referral','month'])) $w->orWhere($idcol, '');
                }
            });
        }
    }

    // ── GET /api/cards/distinct?col=broker — distinct {v,label} for a column filter menu ──
    public function distinctValues(Request $request): JsonResponse
    {
        $user = $request->user();
        $col  = (string) $request->query('col', '');
        if (!isset(self::COLF_SQL[$col])) {
            return response()->json(['success' => false, 'message' => 'عمود غير معروف'], 422);
        }

        $base = DB::table('commission_cards as c')->whereNull('c.deleted_at')
            ->where(fn($w) => $w->whereNull('c.cc_status')->orWhereNotIn('c.cc_status', ['cc_pending','branch_pending','accepted','in_progress','rejected','declined']));
        if ($user->isScopedToBranch())                          $base->where('c.branch_id', $user->branchScopeId());
        elseif ($user->isFinanceAdmin() && $request->branch_id) $base->where('c.branch_id', (int)$request->branch_id);

        $out = [];
        switch ($col) {
            case 'account_number':
                $out = (clone $base)->whereNotNull('c.account_number')->where('c.account_number','!=','')
                    ->distinct()->orderBy('c.account_number')->limit(5000)
                    ->pluck('c.account_number')->map(fn($v) => ['v'=>(string)$v,'label'=>(string)$v])->all();
                break;
            case 'month':
                $rows = (clone $base)->select('c.month')->selectRaw('MAX(c.month_date) md')
                    ->groupBy('c.month')->orderByDesc('md')->get();
                foreach ($rows as $r) $out[] = ($r->month === null || $r->month === '')
                    ? ['v'=>'__blank__','label'=>'(فارغ)'] : ['v'=>(string)$r->month,'label'=>(string)$r->month];
                break;
            case 'account_kind':
                $kinds = (clone $base)->select('c.account_kind')->distinct()->pluck('c.account_kind');
                foreach (['new','sub'] as $kk) if ($kinds->contains($kk)) $out[] = ['v'=>$kk,'label'=>$kk];
                break;
            case 'branch':
                $rows = (clone $base)->leftJoin('branches as b','c.branch_id','=','b.id')
                    ->select('c.branch_id','b.name_ar')->distinct()->get();
                foreach ($rows as $r) $out[] = $r->branch_id === null
                    ? ['v'=>'__blank__','label'=>'(—)'] : ['v'=>(string)$r->branch_id,'label'=>$r->name_ar ?: ('#'.$r->branch_id)];
                usort($out, fn($a,$b) => strcmp($a['label'],$b['label']));
                break;
            case 'broker': case 'marketer': case 'marketer2':
                $idcol = self::COLF_NULLABLE[$col];
                $rows = (clone $base)->leftJoin('employees as e', $idcol, '=', 'e.id')
                    ->selectRaw("{$idcol} as eid, e.name nm")->distinct()->get();
                $hasNull = false;
                foreach ($rows as $r) {
                    if ($r->eid === null) { $hasNull = true; continue; }
                    $out[] = ['v'=>(string)$r->eid,'label'=>$r->nm ?: ('#'.$r->eid)];
                }
                usort($out, fn($a,$b) => strcmp($a['label'],$b['label']));
                if ($hasNull) array_unshift($out, ['v'=>'__blank__','label'=>$col==='marketer2'?'(—)':'(Self)']);
                break;
            case 'referral':
                $rows = (clone $base)->select('c.referral_account')->distinct()->orderBy('c.referral_account')->limit(5000)->get();
                $hasBlank = false;
                foreach ($rows as $r) {
                    if ($r->referral_account === null || $r->referral_account === '') { $hasBlank = true; continue; }
                    $out[] = ['v'=>(string)$r->referral_account,'label'=>(string)$r->referral_account];
                }
                if ($hasBlank) $out[] = ['v'=>'__blank__','label'=>'(—)'];
                break;
            default: // money columns
                $expr = self::COLF_SQL[$col];
                $vals = (clone $base)->selectRaw("{$expr} v")->distinct()->orderByRaw($expr)->limit(5000)->pluck('v');
                foreach ($vals as $v) $out[] = ['v'=>(string)(float)$v,'label'=>number_format((float)$v, 2)];
                break;
        }

        return response()->json(['success' => true, 'col' => $col, 'data' => $out]);
    }

    // ── GET /api/cards/summary ────────────────────────────────
    // Dashboard/report aggregates computed LIVE from commission_cards so they always
    // match the cards list. (The old pre-aggregated card_summaries table drifted out of
    // sync after CC actions / direct deletes, so it is no longer read here.)
    public function summary(Request $request): JsonResponse
    {
        $user = $request->user();

        // Live aggregation straight from commission_cards — ALWAYS consistent with the cards list.
        $base = function () use ($request, $user) {
            $q = DB::table('commission_cards as s')->whereNull('s.deleted_at')
                // A CC card is only a REAL account once the branch completes it. Drafts and
                // in-flight CC leads (cc_pending/branch_pending/accepted/rejected) are phone
                // leads, NOT opened accounts — exclude them so creating a draft never skews
                // the dashboard. Regular (non-CC) cards have cc_status NULL → always counted.
                ->where(fn($w) => $w->whereNull('s.cc_status')->orWhereNotIn('s.cc_status', ['cc_pending','branch_pending','accepted','in_progress','rejected','declined']));
            if ($user->isScopedToBranch())                          $q->where('s.branch_id', $user->branchScopeId());
            elseif ($user->isFinanceAdmin() && $request->branch_id) $q->where('s.branch_id', (int)$request->branch_id);
            if ($from = $request->month_from) { try { $q->whereDate('s.month_date','>=',Carbon::parse("01 {$from}")); } catch (\Exception $e) {} }
            if ($to   = $request->month_to)   { try { $q->whereDate('s.month_date','<=',Carbon::parse("01 {$to}")->endOfMonth()); } catch (\Exception $e) {} }
            return $q;
        };

        // overall totals
        $tot = $base()->selectRaw('
            COUNT(*) cnt, COUNT(DISTINCT s.account_number) uniq,
            SUM(CASE WHEN s.account_kind="new" THEN 1 ELSE 0 END) newc,
            SUM(CASE WHEN s.account_kind="sub" THEN 1 ELSE 0 END) subc,
            SUM(CASE WHEN s.status="modified" THEN 1 ELSE 0 END) modc,
            SUM(CASE WHEN s.cc_branch_id IS NOT NULL THEN 1 ELSE 0 END) ccc,
            SUM(CASE WHEN s.initial_deposit=0 OR s.initial_deposit IS NULL THEN 1 ELSE 0 END) ndc,
            COALESCE(SUM(s.initial_deposit),0) dep, COALESCE(SUM(s.monthly_deposit),0) mon,
            COALESCE(SUM(s.broker_commission),0) bc, COALESCE(SUM(s.marketer_commission),0) mc,
            COALESCE(SUM(s.ext_commission1),0)+COALESCE(SUM(s.ext_commission2),0) ec
        ')->first();

        // per-month rollup (for trend chart) — skip cards with no opening month
        $byMonth = $base()->whereNotNull('s.month')->selectRaw('
            s.month, MAX(s.month_date) md,
            COUNT(*) cnt, COUNT(DISTINCT s.account_number) uniq,
            SUM(CASE WHEN s.account_kind="new" THEN 1 ELSE 0 END) newc,
            SUM(CASE WHEN s.account_kind="sub" THEN 1 ELSE 0 END) subc,
            COALESCE(SUM(s.initial_deposit),0) dep, COALESCE(SUM(s.monthly_deposit),0) mon
        ')->groupBy('s.month')->orderByRaw('MAX(s.month_date)')->get();

        // per-branch rollup (for branch comparison + modifications + empty-account detection)
        $byBranch = $base()->leftJoin('branches as br','s.branch_id','=','br.id')
            ->selectRaw("COALESCE(br.name_ar,'غير محدد') nar, COALESCE(br.name_en,'Unassigned') nen, COUNT(*) cnt, COALESCE(SUM(s.initial_deposit),0) dep, SUM(CASE WHEN s.status='modified' THEN 1 ELSE 0 END) modc, SUM(CASE WHEN s.account_kind='new' THEN 1 ELSE 0 END) newc, SUM(CASE WHEN s.account_kind='sub' THEN 1 ELSE 0 END) subc, SUM(CASE WHEN s.initial_deposit=0 OR s.initial_deposit IS NULL THEN 1 ELSE 0 END) ndc")
            ->groupBy('br.name_ar','br.name_en')->orderByDesc('cnt')->get();

        // per-branch per-month rollup (for stacked monthly chart)
        $byBranchMonth = $base()->leftJoin('branches as br2','s.branch_id','=','br2.id')
            ->whereNotNull('s.month')
            ->selectRaw("COALESCE(br2.name_ar,'غير محدد') nar, COALESCE(br2.name_en,'Unassigned') nen, s.month, MAX(s.month_date) md, SUM(CASE WHEN s.account_kind='new' THEN 1 ELSE 0 END) newc, COUNT(*) cnt")
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
        $topBrokers = collect(); $topMarketers = collect(); $topReferrals = collect(); $commAgg = null;
        try {
            $cc = DB::table('commission_cards as c')->whereNull('c.deleted_at')
                ->where(fn($w) => $w->whereNull('c.cc_status')->orWhereNotIn('c.cc_status', ['cc_pending','branch_pending','accepted','in_progress','rejected','declined']));
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

            $topReferrals = (clone $cc)
                ->whereNotNull('c.referral_account')->where('c.referral_account','!=','')
                ->leftJoin('referrers as rf', 'rf.account_number', '=', 'c.referral_account')
                ->selectRaw("c.referral_account acc, COALESCE(NULLIF(MAX(rf.name),''), c.referral_account) nm, MAX(rf.type) rtype, COUNT(*) total, SUM(CASE WHEN c.account_kind='new' THEN 1 ELSE 0 END) newc, COALESCE(SUM(c.referral_commission),0) comm")
                ->groupBy('c.referral_account')->orderByDesc('total')->orderByDesc('comm')->limit(5)->get();

            // Commission breakdown by role + CC + referral + the $7-per-account ceiling violations
            $sumCols = 'COALESCE(broker_commission,0)+COALESCE(marketer_commission,0)+COALESCE(ext_commission1,0)+COALESCE(ext_commission2,0)+COALESCE(referral_commission,0)+COALESCE(cc_agent_commission,0)+COALESCE(cc_manager_commission,0)';
            $commAgg = (clone $cc)->selectRaw("
                COUNT(*) cnt,
                COALESCE(SUM(broker_commission),0) bc,
                COALESCE(SUM(marketer_commission),0) mc,
                COALESCE(SUM(ext_commission1)+SUM(ext_commission2),0) ec,
                COALESCE(SUM(referral_commission),0) rc,
                COALESCE(SUM(cc_agent_commission)+SUM(cc_manager_commission),0) ccc,
                COALESCE(SUM($sumCols),0) tot_comm,
                SUM(CASE WHEN ($sumCols) > 7.0001 THEN 1 ELSE 0 END) over7,
                COUNT(DISTINCT broker_id) brk, COUNT(DISTINCT marketer_id) mkt
            ")->first();
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
            'commission' => [
                'broker'   => round((float)($commAgg->bc ?? 0), 2),
                'marketer' => round((float)($commAgg->mc ?? 0), 2),
                'external' => round((float)($commAgg->ec ?? 0), 2),
                'referral' => round((float)($commAgg->rc ?? 0), 2),
                'cc'       => round((float)($commAgg->ccc ?? 0), 2),
                'total'    => round((float)($commAgg->tot_comm ?? 0), 2),
                'over7'    => (int)($commAgg->over7 ?? 0),
                'avg'      => ($commAgg && $commAgg->cnt > 0) ? round((float)$commAgg->tot_comm / (int)$commAgg->cnt, 2) : 0,
                'brokers'  => (int)($commAgg->brk ?? 0),
                'marketers'=> (int)($commAgg->mkt ?? 0),
            ],
            'by_month'       => $byMonth,
            'by_branch'      => $byBranch,
            'by_branch_month'=> $byBranchMonth,
            'top_brokers'    => $topBrokers,
            'top_marketers'  => $topMarketers,
            'top_referrals'  => $topReferrals,
            'scope'          => $user->isScopedToBranch() ? ($user->branch?->name_ar ?? 'فرعك') : 'جميع الفروع',
        ]);
    }

    // ── GET /api/cards/over-limit ─────────────────────────────
    // Accounts whose TOTAL per-lot commission exceeds the $7 ceiling (audit list).
    public function overLimit(Request $request): JsonResponse
    {
        $user = $request->user();
        $sumCols = 'COALESCE(c.broker_commission,0)+COALESCE(c.marketer_commission,0)+COALESCE(c.ext_commission1,0)+COALESCE(c.ext_commission2,0)+COALESCE(c.referral_commission,0)+COALESCE(c.cc_agent_commission,0)+COALESCE(c.cc_manager_commission,0)';
        $q = DB::table('commission_cards as c')->whereNull('c.deleted_at')
            ->where(fn($w) => $w->whereNull('c.cc_status')->orWhereNotIn('c.cc_status', ['cc_pending','branch_pending','accepted','in_progress','rejected','declined']))
            ->leftJoin('branches as br','c.branch_id','=','br.id')
            ->leftJoin('employees as bk','c.broker_id','=','bk.id')
            ->leftJoin('employees as mk','c.marketer_id','=','mk.id');
        if ($user->isScopedToBranch())                          $q->where('c.branch_id', $user->branchScopeId());
        elseif ($user->isFinanceAdmin() && $request->branch_id) $q->where('c.branch_id', (int)$request->branch_id);
        $q->whereRaw("($sumCols) > 7.0001");
        $rows = $q->selectRaw("c.id, c.account_number, c.month, br.name_ar branch_ar, br.name_en branch_en,
                bk.name broker_name, mk.name marketer_name,
                c.broker_commission, c.marketer_commission, c.ext_commission1, c.ext_commission2,
                c.referral_commission, c.cc_agent_commission, c.cc_manager_commission,
                ($sumCols) total_comm")
            ->orderByRaw("($sumCols) DESC")->limit(500)->get();
        return response()->json(['success' => true, 'rows' => $rows, 'count' => $rows->count()]);
    }

    // ── GET /api/cards/cross-branch ───────────────────────────
    // Same account number present in MORE THAN ONE branch — a monitoring list for
    // management to decide which branch each account truly belongs to. Finance Admin only.
    public function crossBranch(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user->isFinanceAdmin()) {
            return response()->json(['success' => true, 'count' => 0, 'data' => []]);
        }
        $accounts = DB::table('commission_cards')->whereNull('deleted_at')->whereNotNull('branch_id')
            ->where(fn($w) => $w->whereNull('cc_status')->orWhereNotIn('cc_status', ['cc_pending','branch_pending','accepted','in_progress','rejected','declined']))
            ->select('account_number')->groupBy('account_number')
            ->havingRaw('COUNT(DISTINCT branch_id) > 1')->limit(2000)->pluck('account_number');

        $data = [];
        if ($accounts->isNotEmpty()) {
            $rows = DB::table('commission_cards as c')->whereNull('c.deleted_at')
                ->where(fn($w) => $w->whereNull('c.cc_status')->orWhereNotIn('c.cc_status', ['cc_pending','branch_pending','accepted','in_progress','rejected','declined']))
                ->whereIn('c.account_number', $accounts)
                ->leftJoin('branches as b', 'c.branch_id', '=', 'b.id')
                ->leftJoin('employees as eb', 'c.broker_id', '=', 'eb.id')
                ->leftJoin('employees as em', 'c.marketer_id', '=', 'em.id')
                ->leftJoin('employees as e2', 'c.ext_marketer2_id', '=', 'e2.id')
                ->leftJoin('employees as e1', 'c.ext_marketer1_id', '=', 'e1.id')
                ->get([
                    'c.id', 'c.account_number', 'c.month', 'b.name_ar as bnar', 'b.name_en as bnen',
                    'eb.name as broker', 'c.broker_commission as bc',
                    'em.name as mk1', 'c.marketer_commission as mc',
                    'e2.name as mk2', 'c.ext_commission2 as e2c',
                    'e1.name as ext', 'c.ext_commission1 as e1c',
                    'c.referral_account as ref', 'c.referral_commission as refc',
                ]);
            foreach ($rows->groupBy('account_number') as $acc => $g) {
                // One entry per BRANCH (card) — list every person on that card + their commission.
                $branches = $g->unique('bnar')->map(function ($x) {
                    $people = [];
                    if ($x->broker) $people[] = ['role' => 'broker',    'name' => $x->broker, 'commission' => (float)$x->bc];
                    if ($x->mk1)    $people[] = ['role' => 'marketer1', 'name' => $x->mk1,    'commission' => (float)$x->mc];
                    if ($x->mk2)    $people[] = ['role' => 'marketer2', 'name' => $x->mk2,    'commission' => (float)$x->e2c];
                    if ($x->ext)    $people[] = ['role' => 'external',  'name' => $x->ext,    'commission' => (float)$x->e1c];
                    if ($x->ref)    $people[] = ['role' => 'referral',  'name' => (string)$x->ref, 'commission' => (float)$x->refc];
                    $total = array_sum(array_column($people, 'commission'));
                    return [
                        'id'      => $x->id,
                        'name_ar' => $x->bnar ?: 'غير محدد',
                        'name_en' => $x->bnen ?: 'Unassigned',
                        'month'   => $x->month,
                        'people'  => $people,
                        'total_commission' => round($total, 2),
                    ];
                })->values();
                $data[] = ['account_number' => (string)$acc, 'branch_count' => $branches->count(), 'branches' => $branches];
            }
            usort($data, fn($a, $b) => $b['branch_count'] <=> $a['branch_count']);
        }
        return response()->json(['success' => true, 'count' => count($data), 'data' => $data]);
    }

    // Invalidate every cached fast-list/report (called on ANY card write — including from
    // the Call-Center / Branch / Import controllers, hence public + static).
    public static function bumpCardsVersion(): void
    {
        try {
            $v = (int) Cache::get('cards_cache_ver', 1);
            Cache::forever('cards_cache_ver', $v + 1);
        } catch (\Throwable $e) { /* cache unavailable — ignore */ }
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
        $v = Validator::make($request->all(), $this->validationRules(), $this->validationMessages());
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

        self::bumpCardsVersion();
        $this->saveReferrerName($request);
        return response()->json(['success'=>true,'message'=>"Card #{$card->account_number} created.",'data'=>$card->load($this->with)],201);
    }

    // PUT /api/cards/{id}
    public function update(Request $request, int $id): JsonResponse
    {
        $card = CommissionCard::findOrFail($id);
        $this->assertOwnership($card, $request);

        $v = Validator::make($request->all(), array_merge($this->validationRules(false),
            ['reason'=>'required|string|max:200','notes'=>'nullable|string|max:2000']), $this->validationMessages());
        if ($v->fails()) return response()->json(['success'=>false,'errors'=>$v->errors()],422);

        DB::transaction(function() use ($request, $card) {
            $oldData = $card->only(['broker_id','broker_commission','marketer_id','marketer_commission',
                'ext_marketer1_id','ext_commission1','ext_marketer2_id','ext_commission2',
                'initial_deposit','monthly_deposit','account_status_id','account_kind','parent_account','notes']);

            $fields = $this->extractFields($request);
            // Branch managers cannot relocate a card to another branch
            if ($request->user()->isBranchManager()) unset($fields['branch_id']);

            // Stamp the EDIT month so this card appears in that month's branch report
            $card->update(array_merge($fields, [
                'status'              => 'modified',
                'modified_month'      => now()->format('M Y'),
                'modified_month_date' => now()->startOfMonth()->toDateString(),
            ]));
            $newData = $card->fresh()->only(array_keys($oldData));

            CardModification::create([
                'card_id'=>$card->id,'account_number'=>$card->account_number,'month'=>$card->month,
                'reason'=>$request->reason,'notes'=>$request->notes,
                'old_data'=>$oldData,'new_data'=>$newData,
                'modified_by'=>$request->user()->id,'modified_at'=>now(),
            ]);
            ActivityLog::record('edit_card', $card, ['reason'=>$request->reason,'account'=>$card->account_number]);
        });

        self::bumpCardsVersion();
        $this->saveReferrerName($request);
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
        self::bumpCardsVersion();
        return response()->json(['success'=>true,'message'=>"Card #{$card->account_number} deleted."]);
    }

    // GET /api/cards/tree
    public function tree(Request $request): JsonResponse
    {
        $user  = $request->user();
        // Slim JOIN query (no Eloquent hydration / no N relation queries) mapped to the
        // SAME nested shape the grouping helpers expect — keeps the tree fast at scale.
        $base = DB::table('commission_cards as c')->whereNull('c.deleted_at')
            ->where(fn($w) => $w->whereNull('c.cc_status')->orWhereNotIn('c.cc_status', ['cc_pending','branch_pending','accepted','in_progress','rejected','declined']));
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
                'c.id','c.account_number','c.month','c.month_date','c.modified_month','c.account_kind','c.status',
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
            // Real commission cards only — in-flight CC leads stay in the Call-Center screen.
            $base = DB::table('commission_cards as c')->whereNull('c.deleted_at')
                ->where(fn($w) => $w->whereNull('c.cc_status')->orWhereNotIn('c.cc_status', ['cc_pending','branch_pending','accepted','in_progress','rejected','declined']));
            if ($user->isScopedToBranch())                          $base->where('c.branch_id', $user->branch_id);
            elseif ($user->isFinanceAdmin() && $request->branch_id) $base->where('c.branch_id', (int)$request->branch_id);
            // Month range matches the OPENING month OR the EDIT month — so an edited card
            // shows up in the report for the month it was modified, not only when opened.
            $mFrom = null; $mTo = null;
            if ($from = $request->month_from) { try { $mFrom = Carbon::parse("01 {$from}")->startOfMonth(); } catch(\Exception $e){} }
            if ($to   = $request->month_to)   { try { $mTo   = Carbon::parse("01 {$to}")->endOfMonth();   } catch(\Exception $e){} }
            if ($mFrom || $mTo) {
                $base->where(function ($w) use ($mFrom, $mTo) {
                    $w->where(function ($a) use ($mFrom, $mTo) {
                        if ($mFrom) $a->whereDate('c.month_date', '>=', $mFrom);
                        if ($mTo)   $a->whereDate('c.month_date', '<=', $mTo);
                    })->orWhere(function ($b) use ($mFrom, $mTo) {
                        $b->whereNotNull('c.modified_month_date');
                        if ($mFrom) $b->whereDate('c.modified_month_date', '>=', $mFrom);
                        if ($mTo)   $b->whereDate('c.modified_month_date', '<=', $mTo);
                    });
                });
            }
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
                SUM(CASE WHEN status = "new_added" THEN 1 ELSE 0 END) newadd,
                SUM(CASE WHEN account_kind = "new" THEN 1 ELSE 0 END) kindnew,
                SUM(CASE WHEN account_kind = "sub" THEN 1 ELSE 0 END) kindsub,
                SUM(CASE WHEN cc_branch_id IS NOT NULL THEN 1 ELSE 0 END) ccc,
                SUM(CASE WHEN broker_commission > 5 THEN 1 ELSE 0 END) chigh,
                SUM(CASE WHEN broker_commission > 2 AND broker_commission <= 5 THEN 1 ELSE 0 END) cmid,
                SUM(CASE WHEN broker_commission <= 2 THEN 1 ELSE 0 END) clow
            ')->first();
            $exactCount = (int)($agg->cnt ?? 0);

            // ── Server-side breakdowns for the charts (so the client never processes thousands of rows) ──
            $byBranchR = (clone $base)->leftJoin('branches as brr','c.branch_id','=','brr.id')
                ->selectRaw("COALESCE(brr.name_ar,'غير محدد') nar, COALESCE(brr.name_en,'Unassigned') nen,
                    COUNT(*) cnt, COALESCE(SUM(c.initial_deposit),0) dep")
                ->groupBy('brr.name_ar','brr.name_en')->orderByDesc('cnt')->limit(40)->get();
            $byMonthR = (clone $base)->selectRaw("c.month, MAX(c.month_date) md, COUNT(*) cnt, COALESCE(SUM(c.initial_deposit),0) dep")
                ->groupBy('c.month')->orderByRaw('MAX(c.month_date)')->get();
            $topBrokersR = (clone $base)->leftJoin('employees as bkr','c.broker_id','=','bkr.id')
                ->selectRaw("COALESCE(NULLIF(bkr.name,''),'Self') nm, COUNT(*) cnt, COALESCE(SUM(c.initial_deposit),0) dep")
                ->groupBy('bkr.name')->orderByDesc('cnt')->limit(10)->get();

            // ── Slim page of rows via JOIN (no model hydration, no N relation queries) ──
            $limit = min((int)($request->per_page ?? 5000), 10000);
            $rows = (clone $base)
                ->leftJoin('branches as br','c.branch_id','=','br.id')
                ->leftJoin('employees as bk','c.broker_id','=','bk.id')
                ->leftJoin('employees as mk','c.marketer_id','=','mk.id')
                ->orderByDesc('c.month_date')->orderBy('c.account_number')->limit($limit)
                ->get([
                    'c.id','c.account_number','c.month','c.month_date','c.modified_month','c.account_kind','c.status','c.cc_status','c.cc_branch_id',
                    'c.broker_commission','c.marketer_commission','c.ext_commission1','c.ext_commission2',
                    'c.initial_deposit','c.monthly_deposit',
                    'br.name_ar as branch_ar','br.name_en as branch_en','bk.name as broker_name','mk.name as marketer_name',
                ]);
            // Map to the SAME nested shape the frontend expects (so nothing else changes)
            $data = $rows->map(fn($c) => [
                'id'=>$c->id,'account_number'=>$c->account_number,'month'=>$c->month,'month_date'=>$c->month_date,
                'account_kind'=>$c->account_kind,'status'=>$c->status,'modified_month'=>$c->modified_month,'cc_status'=>$c->cc_status,'cc_branch_id'=>$c->cc_branch_id,
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
                    'new_added_count'       => (int)$agg->newadd,
                    'kind_new'              => (int)$agg->kindnew,
                    'kind_sub'              => (int)$agg->kindsub,
                    'cc_count'              => (int)$agg->ccc,
                    'comm_low'              => (int)$agg->clow,
                    'comm_mid'              => (int)$agg->cmid,
                    'comm_high'             => (int)$agg->chigh,
                ],
                'by_branch'    => $byBranchR,
                'by_month'     => $byMonthR,
                'top_brokers'  => $topBrokersR,
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
        if ($user->isScopedToBranch())
            $query->whereHas('card', fn($q) => $q->where('branch_id', $user->branchScopeId()));
        if ($ac = $request->account_number) $query->where('account_number',$ac);
        return response()->json(['success'=>true,'data'=>$query->paginate(50)]);
    }

    // GET /api/cards/search-flat?q=... — LIGHT flat search (raw JOIN, tiny payload).
    public function searchFlat(Request $request): JsonResponse
    {
        $user = $request->user();
        $term = trim((string) $request->query('q', ''));
        if ($term === '') return response()->json(['success' => true, 'count' => 0, 'data' => []]);

        $query = DB::table('commission_cards as c')
            ->leftJoin('branches as b',   'c.branch_id', '=', 'b.id')
            ->leftJoin('employees as br', 'c.broker_id', '=', 'br.id')
            ->leftJoin('employees as mk', 'c.marketer_id', '=', 'mk.id')
            ->whereNull('c.deleted_at')
            ->where(fn($w) => $w->whereNull('c.cc_status')->orWhereNotIn('c.cc_status', ['cc_pending','branch_pending','accepted','in_progress','rejected','declined']))
            ->where(function ($w) use ($term) {
                $w->where('c.account_number', 'like', "%{$term}%")
                  ->orWhere('br.name', 'like', "%{$term}%")
                  ->orWhere('mk.name', 'like', "%{$term}%")
                  ->orWhere('c.month', 'like', "%{$term}%");
            });

        if ($user->isScopedToBranch()) $query->where('c.branch_id', $user->branchScopeId());

        $rows = $query->orderByDesc('c.month_date')->orderBy('c.account_number')->limit(5000)->get([
            'c.id', 'c.account_number', 'c.month', 'c.status', 'c.account_kind',
            'b.name_ar as branch_ar', 'b.name_en as branch_en',
            'br.name as broker_name', 'c.broker_commission',
            'mk.name as marketer_name', 'c.marketer_commission',
            'c.initial_deposit', 'c.monthly_deposit',
            'c.referral_account', 'c.referral_commission',
        ]);

        return response()->json(['success' => true, 'count' => $rows->count(), 'data' => $rows]);
    }

    // GET /api/cards/modified-report — LIGHT flat list for the Modified Accounts screen.
    // Raw JOIN (no Eloquent, no eager-loaded user photos) → tiny, fast, predictable payload.
    // FA sees all (optional ?branch_id filter); branch users are hard-locked to their branch.
    public function modifiedReport(Request $request): JsonResponse
    {
        $user = $request->user();
        $q = DB::table('card_modifications as m')
            ->join('commission_cards as c', 'm.card_id', '=', 'c.id')
            ->leftJoin('branches as b',  'c.branch_id', '=', 'b.id')
            ->leftJoin('employees as br', 'c.broker_id', '=', 'br.id')
            ->leftJoin('users as u',     'm.modified_by', '=', 'u.id')
            ->whereNull('c.deleted_at')
            ->where(fn($w) => $w->whereNull('c.cc_status')->orWhereNotIn('c.cc_status', ['cc_pending','branch_pending','accepted','in_progress','rejected','declined']));

        if ($user->isScopedToBranch())                          $q->where('c.branch_id', $user->branchScopeId());
        elseif ($user->isFinanceAdmin() && $request->branch_id) $q->where('c.branch_id', (int) $request->branch_id);

        $rows = $q->orderByDesc('m.modified_at')->limit(20000)->get([
            'c.account_number',
            'c.month',
            'c.monthly_deposit',
            'br.name as broker_name',
            'b.name_ar as branch_ar', 'b.name_en as branch_en',
            'm.reason',
            'm.modified_at',
            'u.name as modified_by_name',
        ]);

        return response()->json(['success' => true, 'count' => $rows->count(), 'data' => $rows]);
    }

    // ── Helpers ──
    // GET /api/cards/account-exists?account=6000  → referral lookup
    public function accountExists(Request $request): JsonResponse
    {
        $acc = trim((string) $request->query('account', ''));
        if ($acc === '') return response()->json(['success'=>true,'exists'=>false]);
        $exists = CommissionCard::where('account_number', $acc)->exists();
        return response()->json(['success'=>true,'exists'=>$exists,'account'=>$acc]);
    }

    // GET /api/cards/referrals?account=6000 → accounts this referrer brought
    // GET /api/cards/account-name?account=6000 → the name on that account (its broker, else marketer).
    public function accountName(Request $request): JsonResponse
    {
        $acc = trim((string) $request->query('account', ''));
        if ($acc === '') return response()->json(['success' => true, 'name' => null]);
        // A referrer is a distinct person from the broker/marketer. Their name is NEVER
        // derived from the account's card — it comes ONLY from a manual entry saved in the
        // referrers table. If none exists yet, return null (to be named later from the UI).
        $saved = $this->referrerNameOf($acc);
        return response()->json(['success' => true, 'account' => $acc, 'name' => $saved ?: null, 'source' => $saved ? 'saved' : null]);
    }

    private function referrerNameOf(string $acc): ?string
    {
        try { return DB::table('referrers')->where('account_number', $acc)->value('name'); }
        catch (\Throwable $e) { return null; }
    }

    // Remember a manually-entered referrer name so it auto-fills on any future card.
    private function saveReferrerName(Request $request): void
    {
        $acc = trim((string) ($request->referral_account ?? ''));
        $nm  = trim((string) ($request->referral_name ?? ''));
        if ($acc === '' || $nm === '') return;
        try {
            DB::table('referrers')->updateOrInsert(
                ['account_number' => $acc],
                ['name' => $nm, 'created_by' => optional($request->user())->id, 'created_at' => now(), 'updated_at' => now()]
            );
        } catch (\Throwable $e) { /* referrers table not migrated yet */ }
    }

    // GET /api/cards/referrer-names → [{account, name}] for every account that has referred others.
    // A referrer is a separate person from any broker/marketer, so the name is taken ONLY from the
    // manually-entered referrers table — never derived from a card. Used for the referral datalist,
    // auto-fill on entry, and hover tooltips.
    public function referrerNames(Request $request): JsonResponse
    {
        $user = $request->user();
        $refs = DB::table('commission_cards as c')->whereNull('c.deleted_at')
            ->where(fn($w) => $w->whereNull('c.cc_status')->orWhereNotIn('c.cc_status', ['cc_pending','branch_pending','accepted','in_progress','rejected','declined']))
            ->whereNotNull('c.referral_account')->where('c.referral_account', '!=', '');
        if ($user->isScopedToBranch()) $refs->where('c.branch_id', $user->branchScopeId());
        $accounts = $refs->distinct()->pluck('c.referral_account');
        if ($accounts->isEmpty()) return response()->json(['success' => true, 'data' => []]);

        $map = []; $types = [];
        try {
            $saved = DB::table('referrers')->whereIn('account_number', $accounts)->get(['account_number', 'name', 'type']);
            foreach ($saved as $s) { if ($s->name) $map[$s->account_number] = $s->name; $types[$s->account_number] = $s->type ?? 'client'; }
        } catch (\Throwable $e) { /* table not migrated yet */ }

        $data = $accounts->map(fn($a) => ['account' => (string) $a, 'name' => $map[$a] ?? null, 'type' => $types[$a] ?? 'client'])->values();
        return response()->json(['success' => true, 'count' => $data->count(), 'data' => $data]);
    }

    // POST /api/cards/referrer-name — name and/or classify a single referrer account.
    // Type lives on the account: 'client' (default) or 'employee'. When 'employee', the name
    // is taken from the chosen real employee and the link is stored. One account → one record.
    public function saveReferrer(Request $request): JsonResponse
    {
        $acc  = trim((string) $request->input('account', ''));
        $type = $request->input('type'); // null | 'client' | 'employee'
        if ($acc === '')
            return response()->json(['success' => false, 'message' => 'account is required'], 422);

        $values = ['updated_at' => now(), 'created_by' => optional($request->user())->id];

        if ($type === 'employee') {
            $emp = \App\Models\Employee::find($request->input('employee_id'));
            if (!$emp) return response()->json(['success' => false, 'message' => 'اختر موظفًا صحيحًا'], 422);
            $values['type'] = 'employee';
            $values['employee_id'] = $emp->id;
            $values['name'] = $emp->name;
        } elseif ($type === 'client') {
            $values['type'] = 'client';
            $values['employee_id'] = null;
            $nm = trim((string) $request->input('name', ''));
            if ($nm !== '') $values['name'] = $nm;
        } else {
            // plain rename (no type change) — backward compatible
            $nm = trim((string) $request->input('name', ''));
            if ($nm === '') return response()->json(['success' => false, 'message' => 'name is required'], 422);
            $values['name'] = $nm;
        }

        try {
            DB::table('referrers')->updateOrInsert(['account_number' => $acc],
                array_merge($values, ['created_at' => now()]));
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'referrers table missing'], 500);
        }
        self::bumpCardsVersion();
        return response()->json(['success' => true, 'account' => $acc,
            'name' => $values['name'] ?? null, 'type' => $values['type'] ?? null,
            'employee_id' => $values['employee_id'] ?? null]);
    }

    // POST /api/cards/referrer-names-bulk — upsert many {account,name} pairs at once.
    public function saveReferrersBulk(Request $request): JsonResponse
    {
        $items = $request->input('items', []);
        if (!is_array($items)) return response()->json(['success' => false, 'message' => 'items[] required'], 422);
        $uid = optional($request->user())->id;
        $saved = 0; $skipped = 0;
        foreach ($items as $it) {
            $acc = trim((string) ($it['account'] ?? ''));
            $nm  = trim((string) ($it['name'] ?? ''));
            if ($acc === '' || $nm === '') { $skipped++; continue; }
            try {
                DB::table('referrers')->updateOrInsert(
                    ['account_number' => $acc],
                    ['name' => $nm, 'created_by' => $uid, 'created_at' => now(), 'updated_at' => now()]
                );
                $saved++;
            } catch (\Throwable $e) { $skipped++; }
        }
        self::bumpCardsVersion();
        return response()->json(['success' => true, 'saved' => $saved, 'skipped' => $skipped]);
    }

    public function referrals(Request $request): JsonResponse
    {
        $acc = trim((string) $request->query('account', ''));
        if ($acc === '') return response()->json(['success'=>true,'count'=>0,'total_commission'=>0,'data'=>[]]);

        $q = DB::table('commission_cards as c')
            ->whereNull('c.deleted_at')
            ->where(fn($w) => $w->whereNull('c.cc_status')->orWhereNotIn('c.cc_status', ['cc_pending','branch_pending','accepted','in_progress','rejected','declined']))
            ->where('c.referral_account', $acc);

        $user = $request->user();
        if ($user->isScopedToBranch() && $user->branch_id) $q->where('c.branch_id', $user->branch_id);

        $rows = (clone $q)
            ->leftJoin('branches as b', 'c.branch_id', '=', 'b.id')
            ->orderByDesc('c.month_date')
            ->get(['c.account_number','c.month','c.account_kind','c.referral_commission','b.name_ar as branch']);

        return response()->json([
            'success'          => true,
            'account'          => $acc,
            'count'            => $rows->count(),
            'total_commission' => round((float) $rows->sum('referral_commission'), 2),
            'data'             => $rows,
        ]);
    }

    private function validationRules(bool $required=true): array
    {
        $r = $required ? 'required' : 'sometimes|required';
        return [
            // Account numbers are exactly 6 digits — enforced on every account cell.
            'account_number'=>"{$r}|regex:/^\\d{6}$/",'month'=>"{$r}|string|max:20",'month_date'=>'nullable|date',
            'branch_id'=>'nullable|exists:branches,id','account_type_id'=>'nullable|exists:account_types,id',
            'account_status_id'=>'nullable|exists:account_statuses,id','trading_type_id'=>'nullable|exists:trading_types,id',
            'account_kind'=>'nullable|in:new,sub','parent_account'=>'nullable|regex:/^\\d{6}$/',
            'broker_id'=>'nullable|exists:employees,id','broker_commission'=>'nullable|numeric|min:0',
            'marketer_id'=>'nullable|exists:employees,id','marketer_commission'=>'nullable|numeric|min:0',
            'ext_marketer1_id'=>'nullable|exists:employees,id','ext_commission1'=>'nullable|numeric|min:0',
            'ext_marketer2_id'=>'nullable|exists:employees,id','ext_commission2'=>'nullable|numeric|min:0',
            'referral_account'=>'nullable|regex:/^\\d{6}$/','referral_commission'=>'nullable|numeric|min:0',
            'forex_commission'=>'nullable|numeric|min:0','futures_commission'=>'nullable|numeric|min:0',
            'initial_deposit'=>'nullable|numeric|min:0','monthly_deposit'=>'nullable|numeric|min:0',
            'notes'=>'nullable|string|max:2000',
        ];
    }

    /** Arabic messages for the 6-digit account-number rules. */
    private function validationMessages(): array
    {
        return [
            'account_number.regex'   => 'رقم الحساب يجب أن يتكوّن من 6 أرقام بالضبط.',
            'parent_account.regex'   => 'رقم الحساب الأساسي يجب أن يتكوّن من 6 أرقام بالضبط.',
            'referral_account.regex' => 'رقم حساب المُحيل يجب أن يتكوّن من 6 أرقام بالضبط.',
        ];
    }

    private function extractFields(Request $request): array
    {
        $fields = $request->only([
            'account_number','month','month_date','branch_id',
            'account_type_id','account_status_id','trading_type_id','account_kind','parent_account',
            'broker_id','broker_commission','marketer_id','marketer_commission',
            'ext_marketer1_id','ext_commission1','ext_marketer2_id','ext_commission2',
            'referral_account','referral_commission',
            'forex_commission','futures_commission','initial_deposit','monthly_deposit','notes',
        ]);

        // A primary (new) account has no parent; a sub account keeps its parent number (digits only).
        if (array_key_exists('account_kind', $fields)) {
            if ($fields['account_kind'] === 'new') {
                $fields['parent_account'] = null;
            } elseif ($fields['account_kind'] === 'sub' && isset($fields['parent_account'])) {
                $pa = preg_replace('/[^0-9]/', '', (string) $fields['parent_account']);
                $fields['parent_account'] = $pa !== '' ? $pa : null;
            }
        }

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
