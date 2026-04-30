<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{AccountType, AccountStatus, TradingType, Setting, Employee};
use Illuminate\Http\{Request, JsonResponse};
use Illuminate\Support\Facades\Validator;

class SettingsController extends Controller
{
    // ── Finance Admin guard ───────────────────────────────────
    private function requireFA(Request $request): ?JsonResponse
    {
        if (!$request->user()->isFinanceAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Finance Admin only.',
            ], 403);
        }
        return null;
    }

    // ── GET /api/settings ─────────────────────────────────────
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => [
                'account_types'    => AccountType::where('is_active', true)
                                                 ->orderBy('sort_order')->get(),
                'account_statuses' => AccountStatus::where('is_active', true)
                                                   ->orderBy('sort_order')->get(),
                'trading_types'    => TradingType::where('is_active', true)
                                                 ->orderBy('sort_order')->get(),
            ],
        ]);
    }

    // ── Account Types ─────────────────────────────────────────
    public function storeAccountType(Request $request): JsonResponse
    {
        if ($err = $this->requireFA($request)) return $err;

        $v = Validator::make($request->all(), [
            'name_en' => 'required|string|max:50',
            'name_ar' => 'required|string|max:50',
        ]);
        if ($v->fails()) {
            return response()->json(['success' => false, 'errors' => $v->errors()], 422);
        }

        $item = AccountType::create(
            $request->only('name_en', 'name_ar') +
            ['sort_order' => (AccountType::max('sort_order') ?? 0) + 1]
        );
        return response()->json(['success' => true, 'data' => $item], 201);
    }

    public function destroyAccountType(Request $request, int $id): JsonResponse
    {
        if ($err = $this->requireFA($request)) return $err;
        AccountType::findOrFail($id)->delete();
        return response()->json(['success' => true, 'message' => 'Account type deleted.']);
    }

    // ── Account Statuses ──────────────────────────────────────
    public function storeAccountStatus(Request $request): JsonResponse
    {
        if ($err = $this->requireFA($request)) return $err;

        $v = Validator::make($request->all(), [
            'name_en' => 'required|string|max:50',
            'name_ar' => 'required|string|max:50',
        ]);
        if ($v->fails()) {
            return response()->json(['success' => false, 'errors' => $v->errors()], 422);
        }

        $item = AccountStatus::create(
            $request->only('name_en', 'name_ar') +
            ['sort_order' => (AccountStatus::max('sort_order') ?? 0) + 1]
        );
        return response()->json(['success' => true, 'data' => $item], 201);
    }

    public function destroyAccountStatus(Request $request, int $id): JsonResponse
    {
        if ($err = $this->requireFA($request)) return $err;
        AccountStatus::findOrFail($id)->delete();
        return response()->json(['success' => true, 'message' => 'Account status deleted.']);
    }

    // ── Trading Types ─────────────────────────────────────────
    public function storeTradingType(Request $request): JsonResponse
    {
        if ($err = $this->requireFA($request)) return $err;

        $v = Validator::make($request->all(), [
            'name_en' => 'required|string|max:50',
            'name_ar' => 'required|string|max:50',
        ]);
        if ($v->fails()) {
            return response()->json(['success' => false, 'errors' => $v->errors()], 422);
        }

        $item = TradingType::create(
            $request->only('name_en', 'name_ar') +
            ['sort_order' => (TradingType::max('sort_order') ?? 0) + 1]
        );
        return response()->json(['success' => true, 'data' => $item], 201);
    }

    public function destroyTradingType(Request $request, int $id): JsonResponse
    {
        if ($err = $this->requireFA($request)) return $err;
        TradingType::findOrFail($id)->delete();
        return response()->json(['success' => true, 'message' => 'Trading type deleted.']);
    }

    // ── GET commission limit settings ────────────────────────
    public function getCommissionLimit(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'enabled'       => Setting::commissionLimitEnabled(),
                'amount'        => Setting::commissionLimitAmount(),
                'warning_count' => Setting::commissionWarningCount(),
            ],
        ]);
    }

    // ── POST update commission limit settings ─────────────────
    public function updateCommissionLimit(Request $request): JsonResponse
    {
        if ($err = $this->requireFA($request)) return $err;

        $v = Validator::make($request->all(), [
            'enabled'       => 'required|boolean',
            'amount'        => 'required|numeric|min:1|max:100',
            'warning_count' => 'required|integer|min:1|max:10',
        ]);
        if ($v->fails()) return response()->json(['success'=>false,'errors'=>$v->errors()],422);

        Setting::set('commission_limit_enabled', $request->enabled ? '1' : '0', 'boolean');
        Setting::set('commission_limit_amount',  (string)$request->amount,       'decimal');
        Setting::set('commission_warning_count', (string)$request->warning_count,'integer');

        return response()->json([
            'success' => true,
            'message' => 'Commission limit settings updated.',
            'data' => [
                'enabled'       => (bool) $request->enabled,
                'amount'        => (float) $request->amount,
                'warning_count' => (int) $request->warning_count,
            ],
        ]);
    }

    // ── PUT update CC agent commission per employee ───────────
    public function updateCcCommission(Request $request, int $id): JsonResponse
    {
        if ($err = $this->requireFA($request)) return $err;

        $v = Validator::make($request->all(), [
            'cc_commission' => 'required|numeric|min:0|max:10',
        ]);
        if ($v->fails()) return response()->json(['success'=>false,'errors'=>$v->errors()],422);

        $employee = Employee::findOrFail($id);
        $employee->update(['cc_commission' => $request->cc_commission]);

        return response()->json([
            'success' => true,
            'message' => "CC commission for {$employee->name} updated to \${$request->cc_commission}/lot.",
            'data'    => $employee,
        ]);
    }


    // ── POST /api/settings/account-types ──────────────────────
    public function updateAccountTypes(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'types'            => 'required|array|min:1',
            'types.*.id'       => 'nullable|integer',
            'types.*.name_ar'  => 'required|string|max:60',
            'types.*.name_en'  => 'required|string|max:60',
        ]);
        if ($v->fails()) return response()->json(['success'=>false,'errors'=>$v->errors()],422);

        foreach ($request->types as $item) {
            \App\Models\AccountType::updateOrCreate(
                ['id' => $item['id'] ?? null],
                ['name_ar' => $item['name_ar'], 'name_en' => $item['name_en']]
            );
        }
        return response()->json(['success'=>true,'message'=>'تم تحديث أنواع الحسابات / Account types updated']);
    }

    // ── POST /api/settings/account-statuses ───────────────────
    public function updateAccountStatuses(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'statuses'           => 'required|array|min:1',
            'statuses.*.id'      => 'nullable|integer',
            'statuses.*.name_ar' => 'required|string|max:60',
            'statuses.*.name_en' => 'required|string|max:60',
        ]);
        if ($v->fails()) return response()->json(['success'=>false,'errors'=>$v->errors()],422);

        foreach ($request->statuses as $item) {
            \App\Models\AccountStatus::updateOrCreate(
                ['id' => $item['id'] ?? null],
                ['name_ar' => $item['name_ar'], 'name_en' => $item['name_en']]
            );
        }
        return response()->json(['success'=>true,'message'=>'تم تحديث حالات الحسابات / Account statuses updated']);
    }

    // ── POST /api/settings/trading-types ──────────────────────
    public function updateTradingTypes(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'types'            => 'required|array|min:1',
            'types.*.id'       => 'nullable|integer',
            'types.*.name_ar'  => 'required|string|max:60',
            'types.*.name_en'  => 'required|string|max:60',
        ]);
        if ($v->fails()) return response()->json(['success'=>false,'errors'=>$v->errors()],422);

        foreach ($request->types as $item) {
            \App\Models\TradingType::updateOrCreate(
                ['id' => $item['id'] ?? null],
                ['name_ar' => $item['name_ar'], 'name_en' => $item['name_en']]
            );
        }
        return response()->json(['success'=>true,'message'=>'تم تحديث أنواع التداول / Trading types updated']);
    }

}
