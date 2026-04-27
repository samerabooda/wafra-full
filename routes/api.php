<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\{
    AuthController,
    CommissionCardController,
    DashboardController,
    EmployeeController,
    ManagerController,
    ImportController,
    BranchController,
    SettingsController,
    CallCenterController,
};

/*
|──────────────────────────────────────────────────────────────
|  WAFRA GULF — API Routes
|  Base URL : /api
|  Auth     : Bearer Token (Laravel Sanctum)
|──────────────────────────────────────────────────────────────
*/

// ── Public routes (no authentication) ────────────────────────
Route::prefix('auth')->middleware('throttle:10,1')->group(function () {
    Route::post('register', [AuthController::class, 'register']); // FA first-time only
    Route::post('login',    [AuthController::class, 'login']);
});


// Public: stats for login page brand section (no auth needed)
Route::get('cards/stats', function () {
    return response()->json([
        'success'         => true,
        'total'           => \App\Models\CommissionCard::count(),
        'initial_deposit' => (float) \App\Models\CommissionCard::sum('initial_deposit'),
    ]);
});

// ── Protected routes ──────────────────────────────────────────
Route::middleware(['auth:sanctum', 'active.user', 'throttle:120,1'])->group(function () {

    // Dashboard stats (pre-aggregated, no full-table scan in PHP)
    Route::get('dashboard', [DashboardController::class, 'stats'])
         ->middleware('permission:dashboard');

    // Auth
    Route::prefix('auth')->group(function () {
        Route::post('logout',          [AuthController::class, 'logout']);
        Route::post('change-password', [AuthController::class, 'changePassword']);
        Route::get('me',               [AuthController::class, 'me']);
    });

    // ── Commission Cards ──────────────────────────────────────
    Route::prefix('cards')->group(function () {

        Route::get('tree',          [CommissionCardController::class, 'tree'])
             ->middleware('permission:cards');

        Route::get('report',        [CommissionCardController::class, 'report'])
             ->middleware('permission:reports');

        Route::get('modifications', [CommissionCardController::class, 'modifications'])
             ->middleware('permission:modified');

        Route::get('/',             [CommissionCardController::class, 'index'])
             ->middleware('permission:cards');

        Route::get('{id}',          [CommissionCardController::class, 'show'])
             ->middleware('permission:cards');

        Route::post('/',            [CommissionCardController::class, 'store'])
             ->middleware('permission:create_card');

        Route::put('{id}',          [CommissionCardController::class, 'update'])
             ->middleware('permission:edit_card');

        Route::delete('{id}',       [CommissionCardController::class, 'destroy'])
             ->middleware('role:finance_admin');
    });

    // ── Employees ─────────────────────────────────────────────
    Route::prefix('employees')->group(function () {

        Route::get('pending',        [EmployeeController::class, 'pending'])
             ->middleware('role:finance_admin');

        Route::get('/',              [EmployeeController::class, 'index']);
        Route::get('{id}',           [EmployeeController::class, 'show']);

        Route::post('/',             [EmployeeController::class, 'store'])
             ->middleware('permission:employees');

        Route::put('{id}',           [EmployeeController::class, 'update'])
             ->middleware('permission:employees');

        Route::put('{id}/approve',   [EmployeeController::class, 'approve'])
             ->middleware('role:finance_admin');

        Route::put('{id}/reject',    [EmployeeController::class, 'reject'])
             ->middleware('role:finance_admin');

        Route::delete('{id}',        [EmployeeController::class, 'destroy'])
             ->middleware('permission:employees');
    });

    // ── Managers (Finance Admin only) ─────────────────────────
    Route::prefix('managers')->middleware('role:finance_admin')->group(function () {
        Route::get('/',                       [ManagerController::class, 'index']);
        Route::post('/',                      [ManagerController::class, 'store']);
        Route::put('{id}',                    [ManagerController::class, 'update']);
        Route::delete('{id}',                 [ManagerController::class, 'destroy']);
        Route::post('{id}/reset-password',    [ManagerController::class, 'resetPassword']);
    });

    // ── Branches ──────────────────────────────────────────────
    Route::prefix('branches')->group(function () {
        Route::get('/',     [BranchController::class, 'index']);
        Route::get('{id}',  [BranchController::class, 'show']);
        Route::post('/',    [BranchController::class, 'store'])
             ->middleware('role:finance_admin');
        Route::put('{id}',  [BranchController::class, 'update'])
             ->middleware('role:finance_admin');
    });

    // ── Import ────────────────────────────────────────────────
    // Import — Finance Admin ONLY
    Route::prefix('import')->middleware('role:finance_admin')->group(function () {
        Route::post('/',      [ImportController::class, 'import']);
        Route::get('batches', [ImportController::class, 'batches']);
    });

    // ── Call Center ───────────────────────────────────────────
    Route::prefix('cc')->group(function () {
        // CC branch: create & send cards
        Route::post('cards',                           [CallCenterController::class, 'store']);
        Route::post('cards/{id}/send',                 [CallCenterController::class, 'send']);
        Route::get('sent',                             [CallCenterController::class, 'sent']);

        // Regular branch: receive & respond
        Route::put('cards/{id}/accept',                [CallCenterController::class, 'accept']);
        Route::put('cards/{id}/reject',                [CallCenterController::class, 'reject']);
        Route::put('cards/{id}/complete',              [CallCenterController::class, 'complete']);
        Route::get('pending',                          [CallCenterController::class, 'pending']);

        // Notifications (both branches)
        Route::get('notifications',                    [CallCenterController::class, 'notifications']);
        Route::put('notifications/{id}/read',          [CallCenterController::class, 'markRead']);
    });

    // ── Settings (Lookup tables) ──────────────────────────────
    Route::prefix('settings')->group(function () {
        Route::get('/', [SettingsController::class, 'index']);

        // Finance Admin only for write operations
        Route::post('account-types',         [SettingsController::class, 'storeAccountType'])
             ->middleware('role:finance_admin');
        Route::delete('account-types/{id}',  [SettingsController::class, 'destroyAccountType'])
             ->middleware('role:finance_admin');

        Route::post('account-statuses',       [SettingsController::class, 'storeAccountStatus'])
             ->middleware('role:finance_admin');
        Route::delete('account-statuses/{id}',[SettingsController::class, 'destroyAccountStatus'])
             ->middleware('role:finance_admin');

        Route::post('trading-types',          [SettingsController::class, 'storeTradingType'])
             ->middleware('role:finance_admin');
        Route::delete('trading-types/{id}',   [SettingsController::class, 'destroyTradingType'])
             ->middleware('role:finance_admin');

        // Commission limit settings — FA only
        Route::get('commission-limit',         [SettingsController::class, 'getCommissionLimit'])
             ->middleware('role:finance_admin');
        Route::post('commission-limit',        [SettingsController::class, 'updateCommissionLimit'])
             ->middleware('role:finance_admin');

        // CC agent commission per employee — FA only
        Route::put('employees/{id}/cc-commission', [SettingsController::class, 'updateCcCommission'])
             ->middleware('role:finance_admin');
    });
});
