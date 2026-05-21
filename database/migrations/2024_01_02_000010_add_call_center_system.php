<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Add CC fields to commission_cards ─────────────────────────
        Schema::table('commission_cards', function (Blueprint $table) {
            $table->foreignId('cc_branch_id')
                  ->nullable()->after('branch_id')
                  ->constrained('branches')->nullOnDelete();
            $table->foreignId('cc_agent_id')
                  ->nullable()->after('cc_branch_id')
                  ->constrained('employees')->nullOnDelete();
            $table->decimal('cc_agent_commission', 8, 2)->default(0)->after('cc_agent_id')->comment('CC agent $/lot');
            $table->enum('cc_status', ['cc_pending','branch_pending','accepted','completed','rejected'])
                  ->nullable()->after('cc_agent_commission');
            $table->text('cc_rejection_reason')->nullable()->after('cc_status');

            $table->index('cc_status',    'idx_cc_status');
            $table->index('cc_branch_id', 'idx_cc_branch');
        });

        // ── Create cc_notifications table ─────────────────────────────
        Schema::create('cc_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('card_id')->constrained('commission_cards')->cascadeOnDelete();
            $table->foreignId('from_branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('to_branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('sent_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('responded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type', 50)->comment('card_sent|card_accepted|card_rejected|card_completed');
            $table->enum('status', ['unread','read'])->default('unread');
            $table->text('message');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index('to_branch_id', 'idx_notif_to_branch');
            $table->index('status',       'idx_notif_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cc_notifications');
        Schema::table('commission_cards', function (Blueprint $table) {
            $table->dropIndex('idx_cc_status');
            $table->dropIndex('idx_cc_branch');
            $table->dropColumn(['cc_branch_id','cc_agent_id','cc_agent_commission','cc_status','cc_rejection_reason']);
        });
    }
};
