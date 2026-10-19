<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pre-aggregated summary table — one row per (branch, month).
 *
 * Reports/dashboards read from THIS table instead of scanning the (huge)
 * commission_cards table. Rebuilt hourly by the scheduler and refreshed on
 * write for the affected branch+month. Scales to millions of cards because
 * dashboards never touch the raw rows.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('card_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->index();
            $table->string('month', 20);
            $table->date('month_date')->nullable();

            $table->unsignedInteger('total_count')->default(0);
            $table->unsignedInteger('unique_accounts')->default(0);
            $table->unsignedInteger('new_count')->default(0);
            $table->unsignedInteger('sub_count')->default(0);
            $table->unsignedInteger('modified_count')->default(0);
            $table->unsignedInteger('new_added_count')->default(0);
            $table->unsignedInteger('cc_count')->default(0);

            $table->decimal('total_initial',       18, 2)->default(0);
            $table->decimal('total_monthly',       18, 2)->default(0);
            $table->decimal('total_broker_comm',   18, 2)->default(0);
            $table->decimal('total_marketer_comm', 18, 2)->default(0);
            $table->decimal('total_ext_comm',      18, 2)->default(0);

            $table->timestamp('rebuilt_at')->nullable();
            $table->timestamps();

            // one summary row per branch+month
            $table->unique(['branch_id', 'month'], 'uq_summary_branch_month');
            $table->index('month_date', 'idx_summary_month_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('card_summaries');
    }
};
