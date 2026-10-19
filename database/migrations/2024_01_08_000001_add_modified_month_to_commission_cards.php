<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('commission_cards', function (Blueprint $t) {
            if (!Schema::hasColumn('commission_cards', 'modified_month')) {
                // The month the card was EDITED (so it shows up in that month's branch report).
                $t->string('modified_month', 20)->nullable()->after('month_date');
            }
            if (!Schema::hasColumn('commission_cards', 'modified_month_date')) {
                $t->date('modified_month_date')->nullable()->after('modified_month');
                $t->index('modified_month_date', 'idx_modified_month_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('commission_cards', function (Blueprint $t) {
            if (Schema::hasColumn('commission_cards', 'modified_month')) {
                $t->dropColumn(['modified_month', 'modified_month_date']);
            }
        });
    }
};
