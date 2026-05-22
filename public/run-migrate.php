<?php
/**
 * Temporary one-time migration runner.
 * DELETE THIS FILE AFTER USE.
 * Access: /run-migrate.php?token=WafraMigrate2026
 */

$SECRET = 'WafraMigrate2026';

if (($_GET['token'] ?? '') !== $SECRET) {
    http_response_code(403);
    die('403 Forbidden — wrong token.');
}

// Bootstrap Laravel
define('LARAVEL_START', microtime(true));
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo '<pre style="font-family:monospace;background:#111;color:#0f0;padding:20px;font-size:13px">';
echo "=== WAFRA GULF — MIGRATION RUNNER ===\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n\n";

// Run migration
$exitCode = Artisan::call('migrate', ['--force' => true]);
$output   = Artisan::output();

echo htmlspecialchars($output);
echo "\n=== Exit code: {$exitCode} ===\n";

if ($exitCode === 0) {
    echo "\n✅ Migration completed successfully!\n";
    echo "\n⚠️  IMPORTANT: Delete this file now:\n";
    echo "   /public/run-migrate.php\n";
} else {
    echo "\n❌ Migration failed. Check output above.\n";
}

echo '</pre>';

// Auto-delete after successful run
if ($exitCode === 0 && isset($_GET['autodelete'])) {
    unlink(__FILE__);
    echo '<p style="color:lime;font-family:monospace">✅ File auto-deleted.</p>';
}
