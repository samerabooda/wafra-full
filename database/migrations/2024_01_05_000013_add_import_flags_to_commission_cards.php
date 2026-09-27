<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration 13 — Import flags on commission_cards
 *
 * Adds:
 *  - is_over_limit  → total commissions > $7 (needs FA review)
 *  - is_duplicate   → same account_number + month already existed at import time
 *
 * Both surface in the "needs review" table and paint the row RED
 * in the main commission cards list.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('commission_cards', function (Blueprint $table) {
            $table->boolean('is_over_limit')
                  ->default(false)
                  ->comment('Total commissions exceed $7 limit');
            $table->boolean('is_duplicate')
                  ->default(false)
                  ->comment('Duplicate account_number + month at import');

            // Indexes to speed the "needs review" filter
            $table->index('is_over_limit', 'idx_over_limit');
            $table->index('is_duplicate',  'idx_duplicate');
        });
    }

    public function down(): void
    {
        Schema::table('commission_cards', function (Blueprint $table) {
            $table->dropIndex('idx_over_limit');
            $table->dropIndex('idx_duplicate');
            $table->dropColumn(['is_over_limit', 'is_duplicate']);
        });
    }
};
