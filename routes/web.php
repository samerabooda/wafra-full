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

// --- Temporary workflow-test runner (REMOVE after use) ---
Route::get('/_wf-run/{token}', function (string $token) {
    if ($token !== 'wYsZsNPqrEc1Ufieq-kH1dKRyQfmEosIpS0MKISz0yQ') abort(404);
    @set_time_limit(300); @ini_set('memory_limit', '512M');
    $out = new \Symfony\Component\Console\Output\BufferedOutput();
    $exit = \Artisan::call('test:system-workflow', [], $out);
    $txt = $out->fetch();
    $reports = glob(storage_path('logs/workflow-test-*.md'));
    $latest  = $reports ? end($reports) : null;
    $report  = $latest ? @file_get_contents($latest) : null;
    return response()->json(['exit'=>$exit,'output'=>$txt,'report_path'=>$latest,'report'=>$report], 200, [], JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
})->name('wf.run.temp');
