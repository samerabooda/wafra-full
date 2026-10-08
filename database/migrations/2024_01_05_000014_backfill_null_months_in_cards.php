<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\{DB, Schema};
use Carbon\Carbon;

/**
 * Migration 14 — Backfill NULL `month` values in commission_cards
 *
 * Why: some legacy rows made it into production with month = NULL,
 * which crashes every subsequent edit when card_modifications
 * (month NOT NULL) is written. This migration repairs them in-place
 * using month_date when available, otherwise 'Unknown'.
 *
 * After this, we also force the column to be NOT NULL so no future
 * code path can quietly reintroduce the bug.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1) Backfill — use month_date if present, else literal 'Unknown'
        if (Schema::hasColumn('commission_cards', 'month')) {
            DB::statement("
                UPDATE commission_cards
                SET month = CASE
                    WHEN month_date IS NOT NULL
                        THEN DATE_FORMAT(month_date, '%b %Y')
                    ELSE 'Unknown'
                END
                WHERE month IS NULL OR month = ''
            ");

            // 2) Lock it down — month is required for the whole data model
            try {
                DB::statement("ALTER TABLE commission_cards MODIFY month VARCHAR(20) NOT NULL");
            } catch (\Throwable $e) {
                // Already NOT NULL on some installs — ignore
            }
        }

        // 3) Same safety net for card_modifications: anything left over
        //    with NULL month gets 'Unknown' (zero rows expected but no-cost)
        if (Schema::hasColumn('card_modifications', 'month')) {
            DB::statement("UPDATE card_modifications SET month = 'Unknown' WHERE month IS NULL OR month = ''");
        }
    }

    public function down(): void
    {
        // No reversal — repairing data is not an undoable operation.
    }
};
