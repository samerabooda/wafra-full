<?php
/**
 * ────────────────────────────────────────────────────────────────
 *  Emergency one-off ops endpoint
 * ────────────────────────────────────────────────────────────────
 *  Purpose
 *    - Clear Laravel's compiled Blade / config / route / event caches
 *      from the browser when the Issue-#1 runner is unavailable.
 *    - Idempotently apply the acquisition_type column to
 *      commission_cards when the migration couldn't run through
 *      artisan (same SQL as the migration file, same migrations-
 *      table stamp).
 *
 *  Security
 *    - Fixed strong token in the URL (?t=…). Any other value → 403.
 *    - Logs every hit (allowed OR blocked) to Laravel's log so
 *      misuse is visible. Not meant to live forever — delete this
 *      file in the next commit once the task is done.
 *
 *  Usage
 *    https://<host>/_ops.php?t=<TOKEN>&op=all
 *
 *    op=clear   — clear compiled view / config / route / event caches
 *    op=migrate — add acquisition_type column if missing (safe to re-run)
 *    op=status  — report current column presence + distribution
 *    op=all     — clear + migrate + status (default when 'op' is
 *                 anything else or missing)
 * ────────────────────────────────────────────────────────────────
 */

const OPS_TOKEN = 'c7fa7607fe9b3d8a3180fcec0b43262a';

header('Content-Type: text/plain; charset=utf-8');

$submitted = (string) ($_GET['t'] ?? '');
if (!hash_equals(OPS_TOKEN, $submitted)) {
    http_response_code(403);
    error_log('[_ops.php] denied: bad token from ' . ($_SERVER['REMOTE_ADDR'] ?? '-'));
    echo "forbidden\n";
    exit;
}

// ── Bootstrap Laravel ─────────────────────────────────────────
require __DIR__ . '/../vendor/autoload.php';
$app    = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

$op   = $_GET['op'] ?? 'all';
$log  = [];
$push = function (string $s) use (&$log) { $log[] = $s; };

Log::info('[_ops.php] run', ['op' => $op, 'ip' => $_SERVER['REMOTE_ADDR'] ?? '-']);

// ── Cache cleanup ─────────────────────────────────────────────
if ($op === 'all' || $op === 'clear') {
    $push('── clear ────────────────────────────');
    foreach (['view:clear', 'cache:clear', 'config:clear', 'route:clear', 'event:clear'] as $cmd) {
        try {
            Artisan::call($cmd);
            $push("  ✓ {$cmd}: " . trim(Artisan::output()) ?: 'ok');
        } catch (\Throwable $e) {
            $push("  ✗ {$cmd}: " . $e->getMessage());
        }
    }
    // Reset opcache too — the biggest culprit when files on disk are new but PHP is still running
    // compiled bytecode from the old version.
    if (function_exists('opcache_reset')) {
        $push('  ✓ opcache_reset: ' . (opcache_reset() ? 'ok' : 'no-op'));
    } else {
        $push('  — opcache not installed');
    }
}

// ── Schema patch ──────────────────────────────────────────────
if ($op === 'all' || $op === 'migrate') {
    $push('── migrate ──────────────────────────');
    try {
        if (Schema::hasColumn('commission_cards', 'acquisition_type')) {
            $push('  ✓ acquisition_type column already exists');
        } else {
            DB::statement(
                "ALTER TABLE commission_cards "
                . "ADD COLUMN acquisition_type ENUM('prospected','walk_in') "
                . "NOT NULL DEFAULT 'prospected' "
                . "AFTER account_kind"
            );
            $push('  ✓ acquisition_type column added');
            try {
                DB::statement('ALTER TABLE commission_cards ADD INDEX idx_acq_type (acquisition_type)');
                $push('  ✓ idx_acq_type index added');
            } catch (\Throwable $e) {
                $push('  ⚠︎ index add failed (non-fatal): ' . $e->getMessage());
            }
            $migFile = '2026_11_16_000001_add_acquisition_type_to_commission_cards';
            if (!DB::table('migrations')->where('migration', $migFile)->exists()) {
                $batch = (int) (DB::table('migrations')->max('batch') ?? 0) + 1;
                DB::table('migrations')->insert([
                    'migration' => $migFile,
                    'batch'     => $batch,
                ]);
                $push("  ✓ migrations table stamped (batch {$batch})");
            }
        }
    } catch (\Throwable $e) {
        $push('  ✗ migrate failed: ' . $e->getMessage());
    }
}

// ── Status snapshot ───────────────────────────────────────────
if ($op === 'all' || $op === 'status') {
    $push('── status ───────────────────────────');
    $hasCol = Schema::hasColumn('commission_cards', 'acquisition_type');
    $push('  commission_cards.acquisition_type: ' . ($hasCol ? 'EXISTS ✓' : 'MISSING ✗'));
    if ($hasCol) {
        $rows = DB::table('commission_cards')
            ->whereNull('deleted_at')
            ->selectRaw('acquisition_type, COUNT(*) as n')
            ->groupBy('acquisition_type')->get();
        foreach ($rows as $r) {
            $push("    {$r->acquisition_type}: {$r->n}");
        }
    }
    $total = DB::table('commission_cards')->whereNull('deleted_at')->count();
    $push("  total live cards: {$total}");
}

$push('');
$push('Delete this file after use: public/_ops.php');

echo implode("\n", $log) . "\n";
