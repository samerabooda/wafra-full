<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Composite performance indexes for commission_cards.
 *
 * Designed for the two hot query shapes (and to scale to millions of rows):
 *   1) Admin-wide listing : WHERE deleted_at IS NULL ORDER BY month_date DESC, account_number
 *   2) Branch-scoped list : WHERE branch_id = ? AND deleted_at IS NULL ORDER BY month_date DESC, account_number
 *   3) Summary grouping    : GROUP BY branch_id, month_date
 *
 * Idempotent: each index is added only if it does not already exist, so the
 * migration is safe to re-run and won't collide with the existing single-column
 * indexes from the create migration.
 */
return new class extends Migration
{
    private function indexExists(string $table, string $index): bool
    {
        $db = DB::getDatabaseName();
        $row = DB::selectOne(
            'SELECT COUNT(*) AS c FROM information_schema.statistics
             WHERE table_schema = ? AND table_name = ? AND index_name = ?',
            [$db, $table, $index]
        );
        return $row && (int)$row->c > 0;
    }

    private function addIndex(string $name, string $columns): void
    {
        if (! $this->indexExists('commission_cards', $name)) {
            DB::statement("CREATE INDEX `{$name}` ON `commission_cards` ({$columns})");
        }
    }

    public function up(): void
    {
        // Admin-wide ordered listing (soft-delete aware)
        $this->addIndex('idx_cc_admin_list',  '`deleted_at`, `month_date`, `account_number`');
        // Branch-scoped ordered listing
        $this->addIndex('idx_cc_branch_list', '`branch_id`, `deleted_at`, `month_date`, `account_number`');
        // Summary aggregation per branch + month
        $this->addIndex('idx_cc_branch_month','`branch_id`, `month_date`');
        // Broker reporting / filter (broker_id already has an FK index, add status combo)
        $this->addIndex('idx_cc_status_kind', '`status`, `account_kind`');
    }

    public function down(): void
    {
        foreach (['idx_cc_admin_list','idx_cc_branch_list','idx_cc_branch_month','idx_cc_status_kind'] as $idx) {
            if ($this->indexExists('commission_cards', $idx)) {
                DB::statement("DROP INDEX `{$idx}` ON `commission_cards`");
            }
        }
    }
};
