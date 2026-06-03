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
    Route::get('/callcenter',         [WebController::class,'callcenterIndex'])->name('callcenter.index');
    Route::get('/callcenter/pending', [WebController::class,'callcenterPending'])->name('callcenter.pending');
});
