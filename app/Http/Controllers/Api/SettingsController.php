<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{AccountType, AccountStatus, TradingType};
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
}
