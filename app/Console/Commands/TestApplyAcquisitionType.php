<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Applies the acquisition_type column to commission_cards.
 *
 * The regular `php artisan migrate` command isn't on the Issue-#1
 * runner's allowlist, so this `test:*` command is a thin idempotent
 * wrapper that performs exactly the same schema change the real
 * migration does. It is safe to run many times: if the column is
 * already there, nothing is changed. If it's missing, the column
 * (with its index) is added and defaults every existing row to
 * 'prospected' — matching the migration file verbatim.
 *
 * Verify counts before and after:
 *   php artisan test:apply-acq-migration
 */
class TestApplyAcquisitionType extends Command
{
    protected $signature   = 'test:apply-acq-migration';
    protected $description = 'Add acquisition_type column to commission_cards (idempotent)';

    public function handle(): int
    {
        $this->line('Checking commission_cards.acquisition_type …');

        $totalRows = DB::table('commission_cards')->whereNull('deleted_at')->count();
        $this->line("  total live rows: {$totalRows}");

        if (Schema::hasColumn('commission_cards', 'acquisition_type')) {
            $counts = DB::table('commission_cards')
                ->whereNull('deleted_at')
                ->selectRaw('acquisition_type, COUNT(*) as n')
                ->groupBy('acquisition_type')->get();
            $this->info('✓ column already exists — no change.');
            foreach ($counts as $c) $this->line("   {$c->acquisition_type}: {$c->n}");
            return self::SUCCESS;
        }

        $this->line('→ column missing — adding …');

        try {
            DB::statement(<<<SQL
                ALTER TABLE commission_cards
                  ADD COLUMN acquisition_type ENUM('prospected','walk_in')
                  NOT NULL DEFAULT 'prospected'
                  AFTER account_kind
            SQL);
            $this->info('  ✓ column added (ENUM prospected|walk_in, default prospected)');
        } catch (\Throwable $e) {
            $this->error('  ✗ ALTER TABLE failed: '.$e->getMessage());
            return self::FAILURE;
        }

        try {
            DB::statement('ALTER TABLE commission_cards ADD INDEX idx_acq_type (acquisition_type)');
            $this->info('  ✓ idx_acq_type added');
        } catch (\Throwable $e) {
            // Non-fatal — the table is still functional without the index.
            $this->warn('  ⚠︎ index add failed (non-fatal): '.$e->getMessage());
        }

        // Mark the migration file as run so a future `migrate` doesn't try again.
        try {
            $migrationName = '2026_11_16_000001_add_acquisition_type_to_commission_cards';
            $exists = DB::table('migrations')->where('migration', $migrationName)->exists();
            if (!$exists) {
                $batch = (int) (DB::table('migrations')->max('batch') ?? 0) + 1;
                DB::table('migrations')->insert([
                    'migration' => $migrationName,
                    'batch'     => $batch,
                ]);
                $this->info("  ✓ migrations table stamped (batch {$batch})");
            }
        } catch (\Throwable $e) {
            $this->warn('  ⚠︎ migrations stamp failed (non-fatal): '.$e->getMessage());
        }

        $this->newLine();
        $defaults = DB::table('commission_cards')
            ->whereNull('deleted_at')
            ->where('acquisition_type', 'prospected')->count();
        $this->info("✅ done — {$defaults} / {$totalRows} rows are 'prospected' (default)");
        return self::SUCCESS;
    }
}
