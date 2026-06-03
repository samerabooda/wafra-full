<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-MANAGER notification rows for an accurate tracker.
 * One row per (event, manager) so we can track delivery / read / action
 * precisely per user — unlike the branch-level cc_notifications table.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('manager_notifications')) return;

        Schema::create('manager_notifications', function (Blueprint $t) {
            $t->id();
            $t->string('type', 40)->default('cc_new_card');
            $t->foreignId('card_id')->nullable()->constrained('commission_cards')->nullOnDelete();
            $t->string('account_number', 30)->nullable();
            $t->string('month', 20)->nullable();
            $t->foreignId('from_user_id')->nullable()->constrained('users')->nullOnDelete();
            $t->foreignId('from_branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $t->foreignId('to_user_id')->constrained('users')->cascadeOnDelete();
            $t->foreignId('to_branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $t->string('title', 180);
            $t->text('body')->nullable();
            $t->json('data')->nullable();
            $t->timestamp('delivered_at')->nullable(); // first time the manager's client fetched it
            $t->timestamp('read_at')->nullable();        // manager opened / marked read
            $t->timestamp('acted_at')->nullable();       // manager clicked through / acknowledged
            $t->string('action', 40)->nullable();
            $t->timestamps();

            $t->index(['to_user_id', 'read_at'], 'idx_mn_user_read');
            $t->index('to_branch_id', 'idx_mn_branch');
            $t->index('card_id', 'idx_mn_card');
            $t->index('created_at', 'idx_mn_created');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manager_notifications');
    }
};
