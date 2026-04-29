<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration 11 — Rebate & Referral fields
 *
 * Rebate:   صاحب الحساب يسترد جزءاً من العمولة
 *           → يُحسب ضمن سقف منفصل (has_rebate = true → limit = $7 بدل $8)
 *           → المدير المالي يحدد السقف من الإعدادات
 *
 * Referral: شخص خارجي أحضر العميل، يُعرَّف برقم حسابه فقط
 *           → referral_account: رقم الحساب الخارجي (نص)
 *           → referral_commission: عمولته بالدولار، تدخل ضمن السقف العام
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('commission_cards', function (Blueprint $table) {

            // ── Rebate ────────────────────────────────────────
            $table->boolean('has_rebate')
                  ->default(false)
                  ->after('ext_commission2')
                  ->comment('True = this account has a client rebate');

            $table->decimal('rebate_amount', 8, 2)
                  ->default(0.00)
                  ->after('has_rebate')
                  ->comment('Rebate amount returned to client $/lot');

            // ── Referral ──────────────────────────────────────
            $table->string('referral_account', 50)
                  ->nullable()
                  ->after('rebate_amount')
                  ->comment('External referral account number (text only, no FK)');

            $table->decimal('referral_commission', 8, 2)
                  ->default(0.00)
                  ->after('referral_account')
                  ->comment('Referral commission $/lot — counts toward total limit');

            // ── Indexes ───────────────────────────────────────
            $table->index('has_rebate',        'idx_has_rebate');
            $table->index('referral_account',  'idx_referral_acct');
        });

        // ── Add rebate limit to settings ──────────────────────
        \App\Models\Setting::updateOrCreate(
            ['key' => 'rebate_commission_limit'],
            [
                'value'       => '7.00',
                'type'        => 'decimal',
                'description' => 'الحد الأقصى للعمولات عند وجود Rebate ($/lot)',
            ]
        );
    }

    public function down(): void
    {
        Schema::table('commission_cards', function (Blueprint $table) {
            $table->dropIndex('idx_has_rebate');
            $table->dropIndex('idx_referral_acct');
            $table->dropColumn(['has_rebate','rebate_amount','referral_account','referral_commission']);
        });
        \App\Models\Setting::where('key','rebate_commission_limit')->delete();
    }
};
