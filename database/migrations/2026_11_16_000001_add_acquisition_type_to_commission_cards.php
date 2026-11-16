<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add acquisition_type to commission_cards.
 *
 *   prospected — the marketer actively reached out, qualified and
 *                convinced this client to open an account (default,
 *                the regular path).
 *   walk_in    — the client came to the branch on their own; the
 *                marketer only received them at reception.
 *
 * Commission caps are UNCHANGED by this column. It is a classification
 * tag only: shown as a chip in the UI, filterable in every table, and
 * aggregated separately in dashboard / marketer-comparison reports.
 *
 * Existing rows keep the default `prospected`. A branch manager can
 * re-classify a specific card from the edit screen.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('commission_cards', function (Blueprint $table) {
            $table->enum('acquisition_type', ['prospected', 'walk_in'])
                  ->default('prospected')
                  ->after('account_kind');
            $table->index('acquisition_type', 'idx_acq_type');
        });
    }

    public function down(): void
    {
        Schema::table('commission_cards', function (Blueprint $table) {
            $table->dropIndex('idx_acq_type');
            $table->dropColumn('acquisition_type');
        });
    }
};
