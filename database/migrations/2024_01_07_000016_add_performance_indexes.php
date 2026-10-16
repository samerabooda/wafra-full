<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{Schema, DB};

/**
 * Performance indexes — composite indexes for the queries that run on every
 * page load (branch-scoped lists, dashboards, CC panels). These replace the
 * single-column index scans with range + lookup scans and shave page loads
 * from seconds to tens of milliseconds on the full 8k+ card dataset.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── commission_cards ─────────────────────────────────────
        Schema::table('commission_cards', function (Blueprint $table) {
            // Branch-scoped lists ("my branch's cards, newest first")
            $this->addIndexIfMissing('commission_cards', 'idx_cc_branch_created', ['branch_id', 'created_at']);
            // Branch-scoped reports grouped by month
            $this->addIndexIfMissing('commission_cards', 'idx_cc_branch_monthdate', ['branch_id', 'month_date']);
            // Status filters within a branch (status chips)
            $this->addIndexIfMissing('commission_cards', 'idx_cc_branch_status', ['branch_id', 'status']);
            // Flagged cards view
            $this->addIndexIfMissing('commission_cards', 'idx_cc_flags', ['is_over_limit', 'is_duplicate']);
            // CC panel — "cards assigned to my branch, pending my action"
            $this->addIndexIfMissing('commission_cards', 'idx_cc_branch_cc_status', ['branch_id', 'cc_status']);
            // CC panel — "cards my CC branch has sent out"
            $this->addIndexIfMissing('commission_cards', 'idx_cc_src_cc_status', ['cc_branch_id', 'cc_status']);
            // Broker report
            $this->addIndexIfMissing('commission_cards', 'idx_cc_broker_month', ['broker_id', 'month_date']);
            // Marketer report
            $this->addIndexIfMissing('commission_cards', 'idx_cc_marketer_month', ['marketer_id', 'month_date']);
        });

        // ── cc_notifications ─────────────────────────────────────
        Schema::table('cc_notifications', function (Blueprint $table) {
            // Bell poll — "my unread notifications, newest first"
            $this->addIndexIfMissing('cc_notifications', 'idx_notif_to_status_created', ['to_branch_id', 'status', 'created_at']);
        });

        // ── card_modifications ───────────────────────────────────
        if (Schema::hasTable('card_modifications')) {
            Schema::table('card_modifications', function (Blueprint $table) {
                $this->addIndexIfMissing('card_modifications', 'idx_mod_card_modat', ['card_id', 'modified_at']);
            });
        }

        // ── activity_log ─────────────────────────────────────────
        if (Schema::hasTable('activity_logs')) {
            Schema::table('activity_logs', function (Blueprint $table) {
                $this->addIndexIfMissing('activity_logs', 'idx_act_user_created', ['user_id', 'created_at']);
                $this->addIndexIfMissing('activity_logs', 'idx_act_action_created', ['action', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        // non-destructive — leaving indexes on the way down is safe
    }

    /**
     * Add a composite index only if it doesn't already exist. Avoids the
     * SQLSTATE[42000] 1061 Duplicate key error when the migration runs on a
     * database that may have been partially upgraded by a prior deploy.
     */
    private function addIndexIfMissing(string $table, string $name, array $columns): void
    {
        $exists = collect(DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$name]))->isNotEmpty();
        if ($exists) return;
        $cols = implode(',', array_map(fn($c) => "`{$c}`", $columns));
        DB::statement("CREATE INDEX `{$name}` ON `{$table}` ({$cols})");
    }
};
