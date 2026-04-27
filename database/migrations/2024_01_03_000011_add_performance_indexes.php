<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('commission_cards', function (Blueprint $table) {
            // Soft-delete filter appears on every query
            $table->index('deleted_at', 'idx_deleted_at');

            // Branch manager sees cards by branch — common filter
            $table->index(['branch_id', 'month_date'], 'idx_branch_month');

            // Broker-level reports filtered by month
            $table->index(['broker_id', 'month_date'], 'idx_broker_month');

            // CC workflow: fetch pending cards for a CC branch
            $table->index(['cc_branch_id', 'cc_status'], 'idx_cc_branch_status');
        });

        Schema::table('employees', function (Blueprint $table) {
            // Branch-scoped employee lookups
            $table->index('branch_id', 'idx_emp_branch');
            $table->index('is_active', 'idx_emp_active');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index('branch_id', 'idx_user_branch');
            $table->index('is_active', 'idx_user_active');
        });

        Schema::table('card_modifications', function (Blueprint $table) {
            // Composite for account-level modification history
            $table->index(['account_number', 'modified_at'], 'idx_mod_account_date');
        });
    }

    public function down(): void
    {
        Schema::table('commission_cards', function (Blueprint $table) {
            $table->dropIndex('idx_deleted_at');
            $table->dropIndex('idx_branch_month');
            $table->dropIndex('idx_broker_month');
            $table->dropIndex('idx_cc_branch_status');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropIndex('idx_emp_branch');
            $table->dropIndex('idx_emp_active');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_user_branch');
            $table->dropIndex('idx_user_active');
        });

        Schema::table('card_modifications', function (Blueprint $table) {
            $table->dropIndex('idx_mod_account_date');
        });
    }
};
