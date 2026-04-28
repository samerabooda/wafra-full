<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\Web\WebController;

// Public
Route::get('/', fn() => redirect()->route('auth.login'));
Route::get('/login',  [WebController::class,'loginPage'])->name('auth.login');
Route::post('/login', [WebController::class,'loginPost'])->name('auth.login.submit');
Route::get('/register',  fn()=>redirect()->route('auth.login'))->name('auth.register');
Route::post('/register', [WebController::class,'registerPost'])->name('auth.register.submit');
Route::post('/forgot-password', fn(Request $r)=>back()->with('status','Check your email.'))->name('auth.password.email');

// Auth check endpoint (public)
Route::get('/api-fa-check', [WebController::class,'faCheck'])->name('auth.fa-check');

// Authenticated
Route::middleware('auth')->group(function(){
    Route::post('/logout',         [WebController::class,'logout'])->name('auth.logout');
    Route::get('/dashboard',       [WebController::class,'dashboard'])->name('dashboard');
    Route::get('/cards',           [WebController::class,'cardsIndex'])->name('cards.index');
    Route::get('/cards/create',    [WebController::class,'cardsCreate'])->name('cards.create');
    Route::get('/cards/modified',  [WebController::class,'cardsModified'])->name('cards.modified');
    Route::get('/cards/edit',      [WebController::class,'cardsEditSearch'])->name('cards.edit-search');
    Route::get('/cards/{id}/edit', [WebController::class,'cardsEdit'])->name('cards.edit');
    Route::get('/cards/tree',      [WebController::class,'cardsTree'])->name('cards.tree');
    Route::get('/reports',         [WebController::class,'reports'])->name('reports.index');
    Route::get('/reports/dynamic',  [WebController::class,'reportsDynamic'])->name('reports.dynamic');
    Route::get('/callcenter',          [WebController::class,'callcenter'])->name('callcenter.index');
    Route::get('/callcenter/pending',  [WebController::class,'callcenterPending'])->name('callcenter.pending');
    Route::get('/employees',       [WebController::class,'employees'])->name('employees.index');
    Route::get('/settings',        [WebController::class,'settings'])->name('settings.index');
    Route::get('/import',          [WebController::class,'import'])->name('import.index');
    Route::get('/managers',        [WebController::class,'managers'])->name('managers.index');
    Route::get('/branches',        [WebController::class,'branches'])->name('branches.index');
    Route::get('/permissions',     [WebController::class,'permissions'])->name('permissions.index');
});

// Guide (public within auth)
Route::get('/guide', function() { return view('guide.index'); })->name('guide.index')->middleware('auth');
