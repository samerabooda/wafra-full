<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{CommissionCard, CardModification};
use Illuminate\Http\{Request, JsonResponse};
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    // GET /api/dashboard
    public function stats(Request $request): JsonResponse
    {
        $user = $request->user();

        $base = CommissionCard::query();
        if ($user->isBranchManager() && $user->branch_id) {
            $base->where('branch_id', $user->branch_id);
        }

        // ── 1. KPI summary (one query) ─────────────────────────
        $kpi = (clone $base)->selectRaw(
            'COUNT(*)                             as total,
             COALESCE(SUM(initial_deposit),  0)   as initial_deposit,
             COALESCE(SUM(monthly_deposit),  0)   as monthly_deposit,
             SUM(status = "modified")             as modified,
             SUM(status = "new_added")            as new_added'
        )->first();

        // ── 2. Monthly deposits — last 12 months (GROUP BY) ────
        $monthly = (clone $base)
            ->selectRaw(
                'month,
                 month_date,
                 COALESCE(SUM(initial_deposit), 0) as initial_deposit,
                 COALESCE(SUM(monthly_deposit), 0) as monthly_deposit'
            )
            ->groupBy('month', 'month_date')
            ->orderBy('month_date', 'desc')
            ->limit(12)
            ->get()
            ->sortBy('month_date')  // ascending for the chart
            ->values();

        // ── 3. Broker distribution — top 10 by monthly deposit ─
        $brokerDist = (clone $base)
            ->join('employees as e', 'commission_cards.broker_id', '=', 'e.id')
            ->selectRaw(
                'e.name                                    as broker_name,
                 COUNT(*)                                  as account_count,
                 COALESCE(SUM(commission_cards.monthly_deposit), 0)  as monthly_deposit,
                 COALESCE(SUM(commission_cards.initial_deposit), 0)  as initial_deposit'
            )
            ->whereNotNull('commission_cards.broker_id')
            ->groupBy('e.id', 'e.name')
            ->orderByDesc('monthly_deposit')
            ->limit(10)
            ->get();

        // ── 4. Top marketers by account count ─────────────────
        $topMarketers = (clone $base)
            ->join('employees as em', 'commission_cards.marketer_id', '=', 'em.id')
            ->selectRaw('em.name as marketer_name, COUNT(*) as account_count')
            ->whereNotNull('commission_cards.marketer_id')
            ->groupBy('em.id', 'em.name')
            ->orderByDesc('account_count')
            ->limit(5)
            ->get();

        // ── 5. Top 8 deposits this month ────────────────────────
        $topDeposits = (clone $base)
            ->with(['broker:id,name'])
            ->orderByDesc('monthly_deposit')
            ->limit(8)
            ->get(['account_number', 'month', 'monthly_deposit', 'broker_id']);

        // ── 6. Recent modifications ────────────────────────────
        $modQuery = CardModification::with(['modifiedBy:id,name'])->latest('modified_at');
        if ($user->isBranchManager() && $user->branch_id) {
            $modQuery->join('commission_cards as cc', 'card_modifications.card_id', '=', 'cc.id')
                     ->where('cc.branch_id', $user->branch_id)
                     ->select('card_modifications.*');
        }
        $recentMods = $modQuery->limit(8)->get();

        return response()->json([
            'success' => true,
            'kpi' => [
                'total'           => (int)$kpi->total,
                'initial_deposit' => round((float)$kpi->initial_deposit, 2),
                'monthly_deposit' => round((float)$kpi->monthly_deposit, 2),
                'modified'        => (int)$kpi->modified,
                'new_added'       => (int)$kpi->new_added,
            ],
            'monthly'      => $monthly,
            'broker_dist'  => $brokerDist,
            'top_marketers'=> $topMarketers,
            'top_deposits' => $topDeposits,
            'recent_mods'  => $recentMods,
        ]);
    }
}
