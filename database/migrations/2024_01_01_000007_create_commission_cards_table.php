<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commission_cards', function (Blueprint $table) {
            $table->id();

            // ── Identity ──────────────────────────────────────────
            $table->string('account_number', 30)->comment('AC No.');
            $table->string('month', 20)->comment('e.g. Jan 2025');
            $table->date('month_date')->comment('First day of month for sorting');

            // ── Classification ─────────────────────────────────────
            $table->foreignId('branch_id')
                  ->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('account_type_id')
                  ->nullable()->constrained('account_types')->nullOnDelete();
            $table->foreignId('account_status_id')
                  ->nullable()->constrained('account_statuses')->nullOnDelete();
            $table->foreignId('trading_type_id')
                  ->nullable()->constrained('trading_types')->nullOnDelete();
            $table->enum('account_kind', ['new','sub'])->default('new');

            // ── Broker ─────────────────────────────────────────────
            $table->foreignId('broker_id')
                  ->nullable()->constrained('employees')->nullOnDelete();
            $table->decimal('broker_commission', 8, 2)->default(0)->comment('$/lot');

            // ── Internal Marketer ──────────────────────────────────
            $table->foreignId('marketer_id')
                  ->nullable()->constrained('employees')->nullOnDelete();
            $table->decimal('marketer_commission', 8, 2)->default(0)->comment('$/lot');

            // ── External Marketer 1 ────────────────────────────────
            $table->foreignId('ext_marketer1_id')
                  ->nullable()->constrained('employees')->nullOnDelete();
            $table->decimal('ext_commission1', 8, 2)->default(0)->comment('$/lot');

            // ── External Marketer 2 ────────────────────────────────
            $table->foreignId('ext_marketer2_id')
                  ->nullable()->constrained('employees')->nullOnDelete();
            $table->decimal('ext_commission2', 8, 2)->default(0)->comment('$/lot');

            // ── Forex / Futures ────────────────────────────────────
            $table->decimal('forex_commission', 8, 2)->default(0);
            $table->decimal('futures_commission', 8, 2)->default(0);

            // ── Deposits ───────────────────────────────────────────
            $table->decimal('initial_deposit', 15, 2)->default(0)->comment('Initial Deposit $');
            $table->decimal('monthly_deposit', 15, 2)->default(0)->comment('Monthly Deposit $');

            // ── Status ─────────────────────────────────────────────
            $table->enum('status', ['active','modified','new_added','inactive'])
                  ->default('active');
            $table->text('notes')->nullable();

            // ── Source & Audit ─────────────────────────────────────
            $table->foreignId('import_batch_id')
                  ->nullable()->constrained('import_batches')->nullOnDelete();
            $table->foreignId('created_by')
                  ->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            // ── Constraints ────────────────────────────────────────
            $table->unique(['account_number','month'], 'uq_ac_month');
            $table->index('account_number', 'idx_account');
            $table->index('month',          'idx_month');
            $table->index('month_date',     'idx_month_date');
            $table->index('status',         'idx_status');
            $table->index('account_kind',   'idx_kind');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commission_cards');
    }
};
