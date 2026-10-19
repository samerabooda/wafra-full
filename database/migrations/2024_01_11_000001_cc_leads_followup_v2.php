<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{Schema, DB};

/**
 * Call-Center LEADS workflow. Named class (not anonymous) so the migrator resolves it by
 * name and never depends on the file's require() return value — robust against the cPanel
 * upload adding a UTF-8 BOM. Fully idempotent.
 */
class CcLeadsFollowupV2 extends Migration
{
    public function up(): void
    {
        // 1a) Neutralise the column first: a nullable VARCHAR has no enum/NOT-NULL constraint,
        //     so the conversion can never truncate regardless of the current definition.
        DB::statement("ALTER TABLE commission_cards MODIFY cc_status VARCHAR(20) NULL");

        // 1b) Coerce any value outside the canonical set (e.g. '' / 'none') to NULL.
        DB::statement(
            "UPDATE commission_cards SET cc_status = NULL " .
            "WHERE cc_status NOT IN ('cc_pending','branch_pending','accepted','completed','rejected')"
        );

        // 1c) Apply the widened enum (nullable) with the new lead lifecycle states.
        DB::statement(
            "ALTER TABLE commission_cards MODIFY cc_status " .
            "ENUM('cc_pending','branch_pending','accepted','in_progress','completed','rejected','declined') NULL"
        );

        // 2) Country code for the client phone (Gulf picker; default +965).
        Schema::table('commission_cards', function (Blueprint $t) {
            if (!Schema::hasColumn('commission_cards', 'client_country_code')) {
                $t->string('client_country_code', 8)->nullable()->after('client_phone');
            }
        });

        // 3) Per-lead follow-up tracker (the call log).
        if (!Schema::hasTable('cc_followups')) {
            Schema::create('cc_followups', function (Blueprint $t) {
                $t->id();
                $t->foreignId('card_id')->constrained('commission_cards')->cascadeOnDelete();
                $t->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $t->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
                $t->string('outcome', 30);   // in_progress | opened | declined | reassigned
                $t->text('note')->nullable();
                $t->timestamps();
                $t->index('card_id', 'idx_followup_card');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cc_followups');
        Schema::table('commission_cards', function (Blueprint $t) {
            if (Schema::hasColumn('commission_cards', 'client_country_code')) {
                $t->dropColumn('client_country_code');
            }
        });
        DB::statement(
            "ALTER TABLE commission_cards MODIFY cc_status " .
            "ENUM('cc_pending','branch_pending','accepted','completed','rejected') NULL"
        );
    }
}
