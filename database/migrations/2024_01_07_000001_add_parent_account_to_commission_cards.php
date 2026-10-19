<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('commission_cards', function (Blueprint $t) {
            if (!Schema::hasColumn('commission_cards', 'parent_account')) {
                // For a SUB account: the main/parent account number (from the New/Sub column).
                $t->string('parent_account', 30)->nullable()->after('account_kind');
            }
        });
    }

    public function down(): void
    {
        Schema::table('commission_cards', function (Blueprint $t) {
            if (Schema::hasColumn('commission_cards', 'parent_account')) {
                $t->dropColumn('parent_account');
            }
        });
    }
};
