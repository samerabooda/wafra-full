<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\Web\WebController;
use App\Http\Controllers\Web\PasswordController;

// Public
Route::get('/', fn() => redirect()->route('auth.login'));
Route::get('/login',  [WebController::class,'loginPage'])->name('auth.login');
Route::post('/login', [WebController::class,'loginPost'])
     ->middleware('throttle:10,1')
     ->name('auth.login.submit');
Route::get('/register',  fn()=>redirect()->route('auth.login'))->name('auth.register');
Route::post('/register', [WebController::class,'registerPost'])
     ->middleware('throttle:5,1')
     ->name('auth.register.submit');

// Password Reset
Route::post('/forgot-password',          [PasswordController::class,'sendResetLink'])->name('auth.password.email');
Route::get('/reset-password/{token}',    [PasswordController::class,'showResetForm'])->name('auth.password.reset');
Route::post('/reset-password',           [PasswordController::class,'reset'])->name('auth.password.update');

// Auth check endpoint (public)
Route::get('/api-fa-check', [WebController::class,'faCheck'])->name('auth.fa-check');

// CSRF refresh endpoint — the SPA calls this on 419 to pick up a fresh
// token so long-open pages don't fail with "Page Expired"
Route::get('/csrf-refresh', function (Request $request) {
    $request->session()->regenerateToken();
    return response()->json([
        'success'    => true,
        'csrf_token' => csrf_token(),
    ]);
})->name('csrf.refresh');

// Emergency cache clear — Finance Admin only. Used when auto-deploy's
// `php artisan view:clear` step fails silently (storage permissions).
Route::get('/_cache-clear', function (Request $request) {
    $u = $request->user();
    if (!$u || $u->role !== 'finance_admin') {
        abort(403, 'Finance Admin only.');
    }
    $cleared = [];
    try { \Illuminate\Support\Facades\Artisan::call('view:clear');    $cleared[] = 'view';   } catch (\Throwable $e) {}
    try { \Illuminate\Support\Facades\Artisan::call('config:clear');  $cleared[] = 'config'; } catch (\Throwable $e) {}
    try { \Illuminate\Support\Facades\Artisan::call('route:clear');   $cleared[] = 'route';  } catch (\Throwable $e) {}
    try { \Illuminate\Support\Facades\Artisan::call('cache:clear');   $cleared[] = 'cache';  } catch (\Throwable $e) {}
    // Also brute-force remove compiled Blade views in case artisan can't
    $viewDir = storage_path('framework/views');
    $removed = 0;
    foreach (glob($viewDir . '/*.php') as $f) { @unlink($f) and $removed++; }
    return response()->json([
        'success'        => true,
        'artisan_cleared'=> $cleared,
        'blade_removed'  => $removed,
        'message'        => 'Caches cleared. Reload the page.',
    ]);
})->middleware('auth')->name('cache.clear');

// Authenticated
Route::middleware(['auth','active.user','force.pwd'])->group(function(){
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
    Route::get('/reports/dynamic',        [WebController::class,'reportsDynamic'])->name('reports.dynamic');
    Route::get('/reports/branch-monthly', [WebController::class,'reportsBranchMonthly'])->name('reports.branch-monthly');
    Route::get('/employees',       [WebController::class,'employees'])->name('employees.index');
    Route::get('/settings',        [WebController::class,'settings'])->name('settings.index');
    Route::get('/import',          [WebController::class,'import'])->name('import.index');
    Route::get('/managers',        [WebController::class,'managers'])->name('managers.index');
    Route::get('/branches',        [WebController::class,'branches'])->name('branches.index');
    Route::get('/permissions',     [WebController::class,'permissions'])->name('permissions.index');
    // Guide
    Route::get('/guide', [WebController::class,'guide'])->name('guide.index');
    // Call Center
    Route::get('/callcenter',         [WebController::class,'callcenterIndex'])->name('callcenter.index');
    Route::get('/callcenter/pending', [WebController::class,'callcenterPending'])->name('callcenter.pending');
});
