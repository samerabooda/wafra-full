<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration 15 — Add rebate + referral + parent columns
 *
 * These fields have been referenced in the model's $fillable and in the
 * Export / Dashboard / Import controllers for a while, but the DB columns
 * were never created, so every scenario count that reads `has_rebate` or
 * `referral_account` crashes with "Unknown column", and every import or
 * card create path that tries to persist a referral silently drops it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('commission_cards', function (Blueprint $table) {
            if (!Schema::hasColumn('commission_cards', 'has_rebate')) {
                $table->boolean('has_rebate')
                      ->default(false)
                      ->after('futures_commission')
                      ->comment('Account entitled to rebate');
            }
            if (!Schema::hasColumn('commission_cards', 'rebate_amount')) {
                $table->decimal('rebate_amount', 8, 2)
                      ->default(0)
                      ->after('has_rebate')
                      ->comment('Rebate per lot $');
            }
            if (!Schema::hasColumn('commission_cards', 'referral_account')) {
                $table->string('referral_account', 30)
                      ->nullable()
                      ->after('rebate_amount')
                      ->comment('Account number that referred this client');
            }
            if (!Schema::hasColumn('commission_cards', 'referral_commission')) {
                $table->decimal('referral_commission', 8, 2)
                      ->default(0)
                      ->after('referral_account')
                      ->comment('Referral $/lot');
            }
            if (!Schema::hasColumn('commission_cards', 'parent_account')) {
                $table->string('parent_account', 30)
                      ->nullable()
                      ->after('account_kind')
                      ->comment('Parent AC when account_kind=sub');
            }
        });

        // Index for referral lookups
        try {
            Schema::table('commission_cards', function (Blueprint $table) {
                $table->index('referral_account', 'idx_referral_account');
            });
        } catch (\Throwable $e) {
            // Already indexed on some installs — ignore
        }
    }

    public function down(): void
    {
        Schema::table('commission_cards', function (Blueprint $table) {
            try { $table->dropIndex('idx_referral_account'); } catch (\Throwable $e) {}
            $table->dropColumn([
                'has_rebate', 'rebate_amount',
                'referral_account', 'referral_commission',
                'parent_account',
            ]);
        });
    }
};
