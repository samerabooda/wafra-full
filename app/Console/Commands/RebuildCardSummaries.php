<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

/**
 * Rebuilds the card_summaries table from commission_cards.
 *
 *   php artisan cards:rebuild-summaries            # all branches/months
 *   php artisan cards:rebuild-summaries --branch=3 # single branch (on-write refresh)
 *
 * Runs hourly via the scheduler (routes/console.php) and on-demand after writes.
 * Uses a single GROUP BY aggregate query (index-friendly) + upsert — no row-by-row
 * work, so it stays fast even with millions of cards.
 */
class RebuildCardSummaries extends Command
{
    protected $signature = 'cards:rebuild-summaries {--branch= : Limit to one branch_id}';
    protected $description = 'Rebuild pre-aggregated card summary table (per branch + month)';

    public function handle(): int
    {
        $t0 = microtime(true);

        $q = DB::table('commission_cards')
            ->whereNull('deleted_at')
            ->selectRaw('
                branch_id,
                month,
                MAX(month_date)                                   AS month_date,
                COUNT(*)                                          AS total_count,
                COUNT(DISTINCT account_number)                   AS unique_accounts,
                SUM(CASE WHEN account_kind="new"  THEN 1 ELSE 0 END) AS new_count,
                SUM(CASE WHEN account_kind="sub"  THEN 1 ELSE 0 END) AS sub_count,
                SUM(CASE WHEN status="modified"   THEN 1 ELSE 0 END) AS modified_count,
                SUM(CASE WHEN status="new_added"  THEN 1 ELSE 0 END) AS new_added_count,
                SUM(CASE WHEN cc_branch_id IS NOT NULL THEN 1 ELSE 0 END) AS cc_count,
                SUM(CASE WHEN initial_deposit = 0 OR initial_deposit IS NULL THEN 1 ELSE 0 END) AS no_deposit_count,
                COALESCE(SUM(initial_deposit),0)                 AS total_initial,
                COALESCE(SUM(monthly_deposit),0)                 AS total_monthly,
                COALESCE(SUM(broker_commission),0)               AS total_broker_comm,
                COALESCE(SUM(marketer_commission),0)             AS total_marketer_comm,
                COALESCE(SUM(ext_commission1),0)+COALESCE(SUM(ext_commission2),0) AS total_ext_comm
            ')
            ->groupBy('branch_id', 'month');

        if ($b = $this->option('branch')) {
            $q->where('branch_id', (int) $b);
        }

        $rows = $q->get();
        $now  = now();
        $payload = [];
        foreach ($rows as $r) {
            $payload[] = [
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
            ];
        }

        if ($b = $this->option('branch')) {
            // refresh just this branch's rows
            DB::table('card_summaries')->where('branch_id', (int) $b)->delete();
        } else {
            DB::table('card_summaries')->truncate();
        }

        foreach (array_chunk($payload, 500) as $chunk) {
            DB::table('card_summaries')->insert($chunk);
        }

        // bump the read-cache version so dashboards pick up fresh numbers
        try { Cache::forever('cards_cache_ver', (int) Cache::get('cards_cache_ver', 1) + 1); } catch (\Throwable $e) {}

        $ms = round((microtime(true) - $t0) * 1000);
        $this->info("Rebuilt " . count($payload) . " summary rows in {$ms}ms.");
        return self::SUCCESS;
    }
}
