<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{CommissionCard, Branch, Employee, CardModification};
use Illuminate\Http\{Request, JsonResponse};
use Illuminate\Support\Facades\{DB, Schema};

/**
 * DashboardController — one endpoint, everything the dashboard needs.
 *
 * No `permission:*` middleware — this is the landing page and any
 * authenticated user (FA / branch manager / viewer) should see it.
 * Branch-scoped users (branch_manager, viewer) are auto-scoped to
 * their own branch_id. FA sees everything.
 */
class DashboardController extends Controller
{
    /**
     * GET /api/dashboard
     * Returns aggregated stats + chart data + mini-section tables.
     */
    public function stats(Request $request): JsonResponse
    {
        $user = $request->user();

        $q = CommissionCard::query();

        // Branch scoping — anyone scoped to a branch (branch_manager or
        // branch viewer) sees only their branch's cards. FA sees everything.
        $isScoped = method_exists($user, 'isScopedToBranch') ? $user->isScopedToBranch() : false;
        if ($isScoped && !empty($user->branch_id)) {
            $q->where('branch_id', $user->branch_id);
        }

        // ── Top KPIs ──────────────────────────────────────────
        $totalCards      = (clone $q)->count();
        $totalInitialDep = (float) (clone $q)->sum('initial_deposit');
        $totalMonthlyDep = (float) (clone $q)->sum('monthly_deposit');
        $avgBrokerComm   = (float) (clone $q)->avg('broker_commission');
        $avgMarketerComm = (float) (clone $q)->avg('marketer_commission');
        $thisMonthCards  = (clone $q)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        $modifiedCount   = (clone $q)->where('status', 'modified')->count();
        $newAddedCount   = (clone $q)->where('status', 'new_added')->count();

        // ── Needs Review (over-$7 + duplicates) — only if flags exist ─
        $overLimitCount = Schema::hasColumn('commission_cards','is_over_limit')
                          ? (clone $q)->where('is_over_limit', true)->count() : 0;
        $duplicateCount = Schema::hasColumn('commission_cards','is_duplicate')
                          ? (clone $q)->where('is_duplicate',  true)->count() : 0;
        $needsReviewTotal = $overLimitCount + $duplicateCount;

        // Sample rows for the review mini-tables
        $overLimitRows = Schema::hasColumn('commission_cards','is_over_limit')
            ? (clone $q)->where('is_over_limit', true)
                ->with(['broker:id,name', 'branch:id,name_ar,name_en'])
                ->orderByDesc('created_at')
                ->limit(6)->get()
                ->map(fn($c) => [
                    'id'                  => $c->id,
                    'account_number'      => $c->account_number,
                    'month'               => $c->month,
                    'broker_name'         => $c->broker?->name,
                    'branch_name_ar'      => $c->branch?->name_ar,
                    'branch_name_en'      => $c->branch?->name_en,
                    'broker_commission'   => (float) $c->broker_commission,
                    'marketer_commission' => (float) $c->marketer_commission,
                    'ext_commission1'     => (float) $c->ext_commission1,
                    'ext_commission2'     => (float) $c->ext_commission2,
                ])->values()
            : collect();

        $duplicateRows = Schema::hasColumn('commission_cards','is_duplicate')
            ? (clone $q)->where('is_duplicate', true)
                ->with(['broker:id,name', 'branch:id,name_ar,name_en'])
                ->orderByDesc('created_at')
                ->limit(6)->get()
                ->map(fn($c) => [
                    'id'              => $c->id,
                    'account_number'  => $c->account_number,
                    'month'           => $c->month,
                    'broker_name'     => $c->broker?->name,
                    'branch_name_ar'  => $c->branch?->name_ar,
                    'branch_name_en'  => $c->branch?->name_en,
                ])->values()
            : collect();

        // ── By Branch ─────────────────────────────────────────
        $byBranch = (clone $q)
            ->select('branch_id', DB::raw('COUNT(*) as count'))
            ->groupBy('branch_id')
            ->with('branch:id,name_en,name_ar')
            ->get()
            ->map(fn($row) => [
                'branch_id' => $row->branch_id,
                'name_en'   => $row->branch->name_en ?? '—',
                'name_ar'   => $row->branch->name_ar ?? '—',
                'count'     => (int) $row->count,
            ])
            ->sortByDesc('count')
            ->values();

        // ── Monthly trend (last 12 months) ────────────────────
        $monthlyTrend = (clone $q)
            ->select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(initial_deposit) as initial'),
                DB::raw('SUM(monthly_deposit) as monthly')
            )
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // ── Top Brokers (count + deposit) ─────────────────────
        $topBrokers = (clone $q)
            ->select('broker_id',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(monthly_deposit) as monthly_deposit'))
            ->whereNotNull('broker_id')
            ->groupBy('broker_id')
            ->with('broker:id,name')
            ->orderByDesc('count')
            ->limit(10)
            ->get()
            ->map(fn($row) => [
                'name'            => $row->broker->name ?? '—',
                'count'           => (int) $row->count,
                'monthly_deposit' => (float) $row->monthly_deposit,
            ])
            ->values();

        // ── Top Marketers ─────────────────────────────────────
        $topMarketers = (clone $q)
            ->select('marketer_id', DB::raw('COUNT(*) as count'))
            ->whereNotNull('marketer_id')
            ->groupBy('marketer_id')
            ->with('marketer:id,name')
            ->orderByDesc('count')
            ->limit(10)
            ->get()
            ->map(fn($row) => [
                'name'  => $row->marketer->name ?? '—',
                'count' => (int) $row->count,
            ])
            ->values();

        // ── Recent Modifications ──────────────────────────────
        $modsQuery = CardModification::with(['card.branch:id,name_ar,name_en', 'modifiedBy:id,name'])
                                     ->latest('modified_at');
        if ($isScoped && !empty($user->branch_id)) {
            $modsQuery->whereHas('card', fn($q) => $q->where('branch_id', $user->branch_id));
        }
        $recentModifications = $modsQuery->limit(8)->get()
            ->map(fn($m) => [
                'id'              => $m->id,
                'card_id'         => $m->card_id,
                'account_number'  => $m->account_number,
                'month'           => $m->month,
                'reason'          => $m->reason,
                'modified_at'     => $m->modified_at?->toDateTimeString(),
                'modified_by'     => ['name' => $m->modifiedBy?->name],
                'branch_name_ar'  => $m->card?->branch?->name_ar,
                'branch_name_en'  => $m->card?->branch?->name_en,
            ])->values();

        // ── CC Pending (incoming cards for branch managers; all for FA) ─
        $ccPendingQ = CommissionCard::with(['ccBranch:id,name_ar,name_en', 'ccAgent:id,name'])
                                    ->whereNotNull('cc_branch_id')
                                    ->whereIn('cc_status', ['branch_pending', 'accepted']);
        if ($isScoped && !empty($user->branch_id)) {
            $ccPendingQ->where('branch_id', $user->branch_id);
        }
        $ccPending = $ccPendingQ->orderBy('created_at', 'desc')->limit(8)->get()
            ->map(fn($c) => [
                'id'              => $c->id,
                'account_number'  => $c->account_number,
                'month'           => $c->month,
                'cc_status'       => $c->cc_status,
                'created_at'      => $c->created_at?->toDateTimeString(),
                'cc_agent_name'   => $c->ccAgent?->name,
                'cc_branch_ar'    => $c->ccBranch?->name_ar,
                'cc_branch_en'    => $c->ccBranch?->name_en,
            ])->values();
        $ccPendingCount = $ccPendingQ->count();

        // ── Scenario breakdown ────────────────────────────────
        $scenarios = [
            'rebate'         => Schema::hasColumn('commission_cards','has_rebate')
                                ? (clone $q)->where('has_rebate', true)->count() : 0,
            'referral'       => Schema::hasColumn('commission_cards','referral_account')
                                ? (clone $q)->whereNotNull('referral_account')->where('referral_account','!=','')->count() : 0,
            'cc'             => (clone $q)->whereNotNull('cc_branch_id')->count(),
            'with_marketer'  => (clone $q)->whereNotNull('marketer_id')->count(),
            'external_mkt'   => (clone $q)->whereNotNull('ext_marketer1_id')->count(),
        ];

        return response()->json([
            'success' => true,
            'kpis' => [
                'total_cards'        => $totalCards,
                'total_initial_dep'  => $totalInitialDep,
                'total_monthly_dep'  => $totalMonthlyDep,
                'avg_broker_comm'    => round($avgBrokerComm, 2),
                'avg_marketer_comm'  => round($avgMarketerComm, 2),
                'this_month_cards'   => $thisMonthCards,
                'modified_count'     => $modifiedCount,
                'new_added_count'    => $newAddedCount,
                'needs_review_count' => $needsReviewTotal,
                'over_limit_count'   => $overLimitCount,
                'duplicate_count'    => $duplicateCount,
                'cc_pending_count'   => $ccPendingCount,
            ],
            'by_branch'            => $byBranch,
            'monthly_trend'        => $monthlyTrend,
            'top_brokers'          => $topBrokers,
            'top_marketers'        => $topMarketers,
            'recent_modifications' => $recentModifications,
            'cc_pending'           => $ccPending,
            'over_limit_rows'      => $overLimitRows,
            'duplicate_rows'       => $duplicateRows,
            'scenarios'            => $scenarios,
            'branch_scope'         => $isScoped ? ($user->branch?->name_ar ?? 'فرعك') : 'جميع الفروع',
        ]);
    }
}
