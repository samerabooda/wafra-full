<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Track accounts that were OPENED but never made an initial deposit
 * (initial_deposit = 0). Helps spot branches inflating account counts
 * with empty accounts.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('card_summaries', function (Blueprint $table) {
            if (! Schema::hasColumn('card_summaries', 'no_deposit_count')) {
                $table->unsignedInteger('no_deposit_count')->default(0)->after('cc_count');
            }
        });
    }

    public function down(): void
    {
        Schema::table('card_summaries', function (Blueprint $table) {
            if (Schema::hasColumn('card_summaries', 'no_deposit_count')) {
                $table->dropColumn('no_deposit_count');
            }
        });
    }
};
