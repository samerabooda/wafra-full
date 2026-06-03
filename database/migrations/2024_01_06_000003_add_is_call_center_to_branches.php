<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('branches', function (Blueprint $t) {
            if (!Schema::hasColumn('branches', 'is_call_center')) {
                $t->boolean('is_call_center')->default(false)->after('is_active');
            }
        });
    }

    public function down(): void
    {
        Schema::table('branches', function (Blueprint $t) {
            if (Schema::hasColumn('branches', 'is_call_center')) $t->dropColumn('is_call_center');
        });
    }
};
