<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Call Center Branch System Migration
 *
 * Changes:
 *  1. commission_cards  — 5 new columns for CC workflow
 *  2. employees         — 1 new column: cc_commission (per-employee CC rate)
 *  3. settings          — new table for global system settings
 *  4. notifications     — new table for branch notifications
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── 1. commission_cards — CC columns ──────────────────
        Schema::table('commission_cards', function (Blueprint $table) {

            // Which CC branch sent this card
            $table->foreignId('cc_branch_id')
                  ->nullable()
                  ->after('import_batch_id')
                  ->constrained('branches')
                  ->nullOnDelete()
                  ->comment('Call Center branch that sourced this account');

            // Which CC employee made the call
            $table->foreignId('cc_agent_id')
                  ->nullable()
                  ->after('cc_branch_id')
                  ->constrained('employees')
                  ->nullOnDelete()
                  ->comment('CC employee who sourced the client');

            // CC agent commission (auto-filled from employee.cc_commission)
            $table->decimal('cc_agent_commission', 8, 2)
                  ->default(0.00)
                  ->after('cc_agent_id')
                  ->comment('CC agent commission $/lot — set from employee profile');

            // Workflow status for CC → Branch handoff
            $table->enum('cc_status', [
                'none',           // Not a CC card
                'cc_pending',     // CC created draft, not yet sent
                'branch_pending', // Sent to branch, awaiting accept/reject
                'accepted',       // Branch accepted, completing data
                'rejected',       // Branch rejected with reason
                'completed',      // All data complete, visible to both branches
            ])->default('none')
              ->after('cc_agent_commission');

            // Rejection reason from branch manager
            $table->text('cc_rejection_reason')
                  ->nullable()
                  ->after('cc_status');

            $table->index('cc_branch_id', 'idx_cc_branch');
            $table->index('cc_status',    'idx_cc_status');
        });

        // ── 2. employees — CC commission per employee ──────────
        Schema::table('employees', function (Blueprint $table) {
            $table->decimal('cc_commission', 8, 2)
                  ->default(1.00)
                  ->after('marketing_commission')
                  ->comment('CC agent commission $/lot — set by Finance Admin');
        });

        // ── 3. settings — global system configuration ──────────
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->text('value')->nullable();
            $table->string('type', 20)->default('string')
                  ->comment('string|boolean|decimal|integer');
            $table->string('description', 255)->nullable();
            $table->timestamps();
        });

        // ── 4. cc_notifications — CC ↔ Branch alerts ──────────
        Schema::create('cc_notifications', function (Blueprint $table) {
            $table->id();

            $table->foreignId('card_id')
                  ->constrained('commission_cards')
                  ->cascadeOnDelete();

            $table->foreignId('from_branch_id')
                  ->constrained('branches')
                  ->cascadeOnDelete()
                  ->comment('CC branch sending');

            $table->foreignId('to_branch_id')
                  ->constrained('branches')
                  ->cascadeOnDelete()
                  ->comment('Regular branch receiving');

            $table->foreignId('sent_by')
                  ->constrained('users')
                  ->restrictOnDelete()
                  ->comment('CC user who sent');

            $table->foreignId('responded_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete()
                  ->comment('Branch manager who responded');

            $table->enum('type', ['card_sent', 'card_accepted', 'card_rejected', 'card_completed']);
            $table->enum('status', ['unread', 'read'])->default('unread');
            $table->text('message')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['to_branch_id', 'status'], 'idx_notif_branch');
            $table->index('card_id', 'idx_notif_card');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cc_notifications');
        Schema::dropIfExists('settings');

        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('cc_commission');
        });

        Schema::table('commission_cards', function (Blueprint $table) {
            $table->dropForeign(['cc_branch_id']);
            $table->dropForeign(['cc_agent_id']);
            $table->dropIndex('idx_cc_branch');
            $table->dropIndex('idx_cc_status');
            $table->dropColumn([
                'cc_branch_id', 'cc_agent_id', 'cc_agent_commission',
                'cc_status', 'cc_rejection_reason',
            ]);
        });
    }
};
