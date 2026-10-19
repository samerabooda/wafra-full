<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('commission_cards', function (Blueprint $t) {
            if (!Schema::hasColumn('commission_cards', 'client_phone'))
                $t->string('client_phone', 30)->nullable()->after('notes');
            if (!Schema::hasColumn('commission_cards', 'client_source'))
                $t->string('client_source', 60)->nullable()->after('client_phone');
        });
    }

    public function down(): void
    {
        Schema::table('commission_cards', function (Blueprint $t) {
            if (Schema::hasColumn('commission_cards', 'client_phone'))  $t->dropColumn('client_phone');
            if (Schema::hasColumn('commission_cards', 'client_source')) $t->dropColumn('client_source');
        });
    }
};
