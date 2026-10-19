<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('commission_cards', function (Blueprint $table) {
            if (!Schema::hasColumn('commission_cards', 'referral_account')) {
                $table->string('referral_account', 30)->nullable()->after('ext_commission2');
            }
            if (!Schema::hasColumn('commission_cards', 'referral_commission')) {
                $table->decimal('referral_commission', 12, 2)->default(0)->after('referral_account');
            }
        });
    }

    public function down(): void
    {
        Schema::table('commission_cards', function (Blueprint $table) {
            $table->dropColumn(['referral_account', 'referral_commission']);
        });
    }
};
