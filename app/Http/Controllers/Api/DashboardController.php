<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{CommissionCard, Branch, Employee};
use Illuminate\Http\{Request, JsonResponse};
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * GET /api/dashboard
     * Returns aggregated stats + chart data for the dashboard page.
     * Auto-scopes to the caller's branch for branch managers.
     */
    public function stats(Request $request): JsonResponse
    {
        $user = $request->user();

        $q = CommissionCard::query();
        if (method_exists(CommissionCard::class, 'applyBranchScope')) {
            $q = CommissionCard::applyBranchScope($q, $user);
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
                'count'     => $row->count,
            ]);

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

        // ── Top Brokers ───────────────────────────────────────
        $topBrokers = (clone $q)
            ->select('broker_id', DB::raw('COUNT(*) as count'))
            ->whereNotNull('broker_id')
            ->groupBy('broker_id')
            ->with('broker:id,name')
            ->orderByDesc('count')
            ->limit(10)
            ->get()
            ->map(fn($row) => [
                'name'  => $row->broker->name ?? '—',
                'count' => $row->count,
            ]);

        // ── Scenario breakdown ────────────────────────────────
        $scenarios = [
            'rebate'         => (clone $q)->where('has_rebate', true)->count(),
            'referral'       => (clone $q)->whereNotNull('referral_account')->count(),
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
            ],
            'by_branch'      => $byBranch,
            'monthly_trend'  => $monthlyTrend,
            'top_brokers'    => $topBrokers,
            'scenarios'      => $scenarios,
        ]);
    }
}
