<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\Web\WebController;
use App\Http\Controllers\Web\PasswordController;

// Public
Route::get('/', fn() => redirect()->route('auth.login'));
Route::get('/login',  [WebController::class,'loginPage'])->name('auth.login');
Route::post('/login', [WebController::class,'loginPost'])->name('auth.login.submit');
Route::get('/register',  fn()=>redirect()->route('auth.login'))->name('auth.register');
Route::post('/register', [WebController::class,'registerPost'])->name('auth.register.submit');

// Password Reset
Route::post('/forgot-password',          [PasswordController::class,'sendResetLink'])->name('auth.password.email');
Route::get('/reset-password/{token}',    [PasswordController::class,'showResetForm'])->name('auth.password.reset');
Route::post('/reset-password',           [PasswordController::class,'reset'])->name('auth.password.update');

// Auth check endpoint (public)
Route::get('/api-fa-check', [WebController::class,'faCheck'])->name('auth.fa-check');

// ─────────────────────────────────────────────────────────────
// Emergency one-off ops endpoint. Token-guarded, bypasses auth.
// Lets the operator clear Laravel's compiled caches + apply the
// acquisition_type column from a browser when the Issue-#1 runner
// is unavailable (as happened in Nov 2026).
// Will be removed in the commit following the fix.
// ─────────────────────────────────────────────────────────────
Route::get('/_ops', function (Request $request) {
    $TOKEN = 'c7fa7607fe9b3d8a3180fcec0b43262a';
    if (!hash_equals($TOKEN, (string) $request->query('t', ''))) {
        \Illuminate\Support\Facades\Log::warning('[_ops] denied', ['ip' => $request->ip()]);
        return response('forbidden', 403)->header('Content-Type', 'text/plain');
    }
    \Illuminate\Support\Facades\Log::info('[_ops] run', ['op' => $request->query('op'), 'ip' => $request->ip()]);

    $op  = (string) $request->query('op', 'all');
    $log = [];
    $out = function (string $s) use (&$log) { $log[] = $s; };

    if ($op === 'all' || $op === 'clear') {
        $out('── clear ────────────────────────────');
        foreach (['view:clear','cache:clear','config:clear','route:clear','event:clear'] as $cmd) {
            try {
                \Illuminate\Support\Facades\Artisan::call($cmd);
                $out("  ✓ {$cmd}: " . (trim(\Illuminate\Support\Facades\Artisan::output()) ?: 'ok'));
            } catch (\Throwable $e) {
                $out("  ✗ {$cmd}: " . $e->getMessage());
            }
        }
        if (function_exists('opcache_reset')) {
            $out('  ✓ opcache_reset: ' . (opcache_reset() ? 'ok' : 'no-op'));
        } else {
            $out('  — opcache not installed');
        }
    }

    if ($op === 'all' || $op === 'migrate') {
        $out('── migrate ──────────────────────────');
        try {
            if (\Illuminate\Support\Facades\Schema::hasColumn('commission_cards', 'acquisition_type')) {
                $out('  ✓ acquisition_type column already exists');
            } else {
                \Illuminate\Support\Facades\DB::statement(
                    "ALTER TABLE commission_cards "
                    . "ADD COLUMN acquisition_type ENUM('prospected','walk_in') "
                    . "NOT NULL DEFAULT 'prospected' "
                    . "AFTER account_kind"
                );
                $out('  ✓ acquisition_type column added');
                try {
                    \Illuminate\Support\Facades\DB::statement(
                        'ALTER TABLE commission_cards ADD INDEX idx_acq_type (acquisition_type)'
                    );
                    $out('  ✓ idx_acq_type index added');
                } catch (\Throwable $e) {
                    $out('  ⚠︎ index add failed (non-fatal): ' . $e->getMessage());
                }
                $migFile = '2026_11_16_000001_add_acquisition_type_to_commission_cards';
                if (!\Illuminate\Support\Facades\DB::table('migrations')->where('migration', $migFile)->exists()) {
                    $batch = (int) (\Illuminate\Support\Facades\DB::table('migrations')->max('batch') ?? 0) + 1;
                    \Illuminate\Support\Facades\DB::table('migrations')->insert([
                        'migration' => $migFile,
                        'batch'     => $batch,
                    ]);
                    $out("  ✓ migrations table stamped (batch {$batch})");
                }
            }
        } catch (\Throwable $e) {
            $out('  ✗ migrate failed: ' . $e->getMessage());
        }
    }

    if ($op === 'all' || $op === 'status') {
        $out('── status ───────────────────────────');
        $hasCol = \Illuminate\Support\Facades\Schema::hasColumn('commission_cards', 'acquisition_type');
        $out('  commission_cards.acquisition_type: ' . ($hasCol ? 'EXISTS ✓' : 'MISSING ✗'));
        if ($hasCol) {
            $rows = \Illuminate\Support\Facades\DB::table('commission_cards')
                ->whereNull('deleted_at')
                ->selectRaw('acquisition_type, COUNT(*) as n')
                ->groupBy('acquisition_type')->get();
            foreach ($rows as $r) $out("    {$r->acquisition_type}: {$r->n}");
        }
        $tot = \Illuminate\Support\Facades\DB::table('commission_cards')
            ->whereNull('deleted_at')->count();
        $out("  total live cards: {$tot}");
        $out('  git HEAD on server: ' . trim(@shell_exec('git -C ' . escapeshellarg(base_path()) . ' rev-parse --short HEAD 2>&1') ?: 'unknown'));
    }

    $out('');
    $out('Delete this route after use (routes/web.php — the /_ops block).');

    return response(implode("\n", $log) . "\n")
        ->header('Content-Type', 'text/plain; charset=utf-8')
        ->header('Cache-Control', 'no-store, max-age=0');
})->withoutMiddleware([
    \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
]);
// ─── end _ops ──────────────────────────────────────────────────

// Fresh CSRF token (prevents recurring 419 on a stale/restored login page)
Route::get('/refresh-token', fn() => response(csrf_token())
    ->header('Cache-Control', 'no-store, max-age=0'));

// Small company logo for emails — served from a committed base64 file (deploy-safe, no binary
// upload, independent of static-file serving). Used by the invite/notification emails.
Route::get('/assets/email-logo.png', function () {
    $b64 = @file_get_contents(resource_path('assets/email-logo.b64'));
    if (!$b64) abort(404);
    $png = base64_decode(trim($b64));
    return response($png, 200)
        ->header('Content-Type', 'image/png')
        ->header('Cache-Control', 'public, max-age=2592000');
})->name('email.logo');

// Authenticated
Route::middleware('auth')->group(function(){
    Route::post('/logout',         [WebController::class,'logout'])->name('auth.logout');
    Route::get('/dashboard',       [WebController::class,'dashboard'])->name('dashboard');
    Route::get('/cards',           [WebController::class,'cardsIndex'])->name('cards.index');
    Route::get('/cards/create',    [WebController::class,'cardsCreate'])->name('cards.create');
    Route::get('/cards/modified',  [WebController::class,'cardsModified'])->name('cards.modified');
    Route::get('/cards/search',    [WebController::class,'cardsSearch'])->name('cards.search');
    Route::get('/cards/edit',      [WebController::class,'cardsEditSearch'])->name('cards.edit-search');
    Route::get('/cards/{id}/edit', [WebController::class,'cardsEdit'])->name('cards.edit');
    Route::get('/cards/tree',      [WebController::class,'cardsTree'])->name('cards.tree');
    Route::get('/reports',                [WebController::class,'reports'])->name('reports.index');
    Route::get('/reports/table',          [WebController::class,'reportsTable'])->name('reports.table');
    Route::get('/reports/dynamic',        [WebController::class,'reportsDynamic'])->name('reports.dynamic');
    Route::get('/reports/branch-monthly', [WebController::class,'reportsBranchMonthly'])->name('reports.branch-monthly');
    Route::get('/reports/referrals',      [WebController::class,'reportsReferrals'])->name('reports.referrals');
    Route::get('/employees',       [WebController::class,'employees'])->name('employees.index');
    Route::get('/settings',        [WebController::class,'settings'])->name('settings.index');
    Route::get('/import',          [WebController::class,'import'])->name('import.index');
    Route::get('/managers',        [WebController::class,'managers'])->name('managers.index');
    Route::get('/branches',        [WebController::class,'branches'])->name('branches.index');
    Route::get('/permissions',     [WebController::class,'permissions'])->name('permissions.index');
    // Profile
    Route::get('/profile', [WebController::class,'profile'])->name('profile.index');
    // Notifications tracker (Finance Admin) + mailbox/inbox (all roles)
    Route::get('/notifications/tracker', [WebController::class,'notificationsTracker'])->name('notifications.tracker');
    Route::get('/notifications/inbox',   [WebController::class,'notificationsInbox'])->name('notifications.inbox');
    // Guide
    Route::get('/guide', [WebController::class,'guide'])->name('guide.index');
    // Call Center
    Route::get('/callcenter',          [WebController::class,'callcenterIndex'])->name('callcenter.index');
    Route::get('/callcenter/pending',  [WebController::class,'callcenterPending'])->name('callcenter.pending');
    Route::get('/callcenter/followup', [WebController::class,'callcenterFollowup'])->name('callcenter.followup');
});
