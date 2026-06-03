<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{Branch, AccountType, AccountStatus, TradingType, ActivityLog};
use Illuminate\Http\{Request, JsonResponse};
use Illuminate\Support\Facades\{Validator, DB};
use Illuminate\Validation\Rule;

// ── BranchController ──────────────────────────────────────────
class BranchController extends Controller
{
    // GET /api/branches
    public function index(Request $request): JsonResponse
    {
        $branches = Branch::withCount(['employees', 'commissionCards'])
                          ->orderBy('code')
                          ->get();

        return response()->json(['success' => true, 'data' => $branches]);
    }

    // GET /api/branches/{id}
    public function show(int $id): JsonResponse
    {
        $branch = Branch::withCount(['employees', 'commissionCards'])->findOrFail($id);
        return response()->json(['success' => true, 'data' => $branch]);
    }

    // POST /api/branches  (Finance Admin only)
    public function store(Request $request): JsonResponse
    {
        if (!$request->user()->isFinanceAdmin()) {
            return response()->json(['success' => false, 'message' => 'Finance Admin only.'], 403);
        }

        $v = Validator::make($request->all(), [
            // Ignore soft-deleted records in uniqueness check
            'code'    => ['required', 'string', 'max:20',
                          Rule::unique('branches', 'code')->whereNull('deleted_at')],
            'name_ar' => 'required|string|max:100',
            'name_en' => 'required|string|max:100',
            'country' => 'nullable|string|max:50',
            'city'    => 'nullable|string|max:50',
            'is_call_center' => 'nullable|boolean',
        ]);

        if ($v->fails()) {
            return response()->json(['success' => false, 'errors' => $v->errors()], 422);
        }

        $branch = Branch::create([
            ...$request->only('code', 'name_ar', 'name_en', 'country', 'city'),
            'is_call_center' => $request->boolean('is_call_center'),
            'created_by'     => $request->user()->id,
        ]);

        ActivityLog::record('create_branch', $branch);

        return response()->json([
            'success' => true,
            'message' => "Branch {$branch->name_ar} created.",
            'data'    => $branch,
        ], 201);
    }

    // PUT /api/branches/{id}  (Finance Admin only)
    public function update(Request $request, int $id): JsonResponse
    {
        if (!$request->user()->isFinanceAdmin()) {
            return response()->json(['success' => false, 'message' => 'Finance Admin only.'], 403);
        }

        $branch = Branch::findOrFail($id);
        $v = Validator::make($request->all(), [
            'code'      => ['sometimes','required','string','max:20',
                            Rule::unique('branches','code')->ignore($branch->id)->whereNull('deleted_at')],
            'name_ar'   => 'sometimes|string|max:100',
            'name_en'   => 'sometimes|string|max:100',
            'country'   => 'nullable|string|max:50',
            'city'      => 'nullable|string|max:50',
            'is_active' => 'nullable|boolean',
            'is_call_center' => 'nullable|boolean',
        ]);

        if ($v->fails()) {
            return response()->json(['success' => false, 'errors' => $v->errors()], 422);
        }

        $branch->update($request->only('code', 'name_ar', 'name_en', 'country', 'city', 'is_active', 'is_call_center'));
        ActivityLog::record('update_branch', $branch);

        return response()->json(['success' => true, 'data' => $branch]);
    }

    // DELETE /api/branches/{id}  (Finance Admin only)
    // Uses forceDelete() so the code can be reused immediately.
    // commission_cards.branch_id → set to NULL via DB trigger/FK nullOnDelete.
    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        if (!$user->isFinanceAdmin()) {
            return response()->json(['success' => false, 'message' => 'Finance Admin only.'], 403);
        }

        $branch = Branch::withCount(['commissionCards', 'employees'])->findOrFail($id);
        $cardCount = $branch->commission_cards_count;
        $empCount  = $branch->employees_count;

        // ── Require the Finance Admin's login password to confirm ──
        if (!$request->filled('password') || !\Illuminate\Support\Facades\Hash::check($request->password, $user->password)) {
            return response()->json(['success' => false, 'message' => 'كلمة مرور المدير المالي غير صحيحة.'], 403);
        }

        // ── Where do the commission cards go? ──
        $targetId = $request->target_branch_id ? (int) $request->target_branch_id : null;
        if ($cardCount > 0 && !$targetId) {
            return response()->json(['success' => false, 'message' => 'اختر الفرع الذي ستُنقل إليه كروت العمولة.'], 422);
        }
        if ($targetId) {
            if ($targetId === $id) {
                return response()->json(['success' => false, 'message' => 'لا يمكن النقل لنفس الفرع المحذوف.'], 422);
            }
            if (!Branch::whereKey($targetId)->exists()) {
                return response()->json(['success' => false, 'message' => 'الفرع الوجهة غير موجود.'], 422);
            }
        }

        DB::transaction(function () use ($id, $targetId, $branch) {
            if ($targetId) {
                // Move the cards (and this branch's employees) to the chosen branch
                DB::table('commission_cards')->where('branch_id', $id)->update(['branch_id' => $targetId]);
                DB::table('employees')->where('branch_id', $id)->update(['branch_id' => $targetId]);
            } else {
                DB::table('employees')->where('branch_id', $id)->update(['branch_id' => null]);
            }
            // Managers/users of the deleted branch are detached (FA reassigns them)
            DB::table('users')->where('branch_id', $id)->update(['branch_id' => null]);

            ActivityLog::record('delete_branch', null, [
                'id' => $id, 'name_ar' => $branch->name_ar, 'code' => $branch->code,
                'moved_cards_to' => $targetId, 'card_count' => $branch->commission_cards_count,
            ]);
            $branch->forceDelete();
        });

        // refresh the target branch's summary so the moved cards show up
        if ($targetId) { try { \App\Http\Controllers\Api\CommissionCardController::refreshBranchSummary($targetId); } catch (\Throwable $e) {} }

        $target = $targetId ? Branch::find($targetId) : null;
        $msg = "✅ تم حذف الفرع \"{$branch->name_ar}\" نهائياً.";
        if ($cardCount > 0 && $target) $msg .= " نُقل {$cardCount} كرت إلى \"{$target->name_ar}\".";

        return response()->json(['success' => true, 'message' => $msg]);
    }
}
