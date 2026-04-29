<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Performance indexes for commission_cards table
 * Supports millions of rows with fast queries
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('commission_cards', function (Blueprint $table) {
            // Primary lookups
            $table->index(['branch_id', 'month_date'],    'idx_branch_month');
            $table->index(['branch_id', 'status'],        'idx_branch_status');
            $table->index(['broker_id', 'month_date'],    'idx_broker_month');
            $table->index(['marketer_id', 'month_date'],  'idx_marketer_month');

            // CC workflow
            $table->index(['cc_branch_id', 'cc_status'],  'idx_cc_branch_status');
            $table->index(['cc_status', 'branch_id'],     'idx_cc_status_branch');

            // Reports & filters
            $table->index(['month_date', 'status'],       'idx_month_status');
            $table->index(['created_by', 'created_at'],   'idx_created_by_at');
            $table->index(['account_number'],              'idx_account_number');
            $table->index(['deleted_at'],                  'idx_soft_delete');

            // Rebate / Referral queries
            $table->index(['has_rebate', 'branch_id'],    'idx_rebate_branch');
        });
    }

    public function down(): void
    {
        Schema::table('commission_cards', function (Blueprint $table) {
            $table->dropIndex('idx_branch_month');
            $table->dropIndex('idx_branch_status');
            $table->dropIndex('idx_broker_month');
            $table->dropIndex('idx_marketer_month');
            $table->dropIndex('idx_cc_branch_status');
            $table->dropIndex('idx_cc_status_branch');
            $table->dropIndex('idx_month_status');
            $table->dropIndex('idx_created_by_at');
            $table->dropIndex('idx_account_number');
            $table->dropIndex('idx_soft_delete');
            $table->dropIndex('idx_rebate_branch');
        });
    }
};
