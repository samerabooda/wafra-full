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
        ]);

        if ($v->fails()) {
            return response()->json(['success' => false, 'errors' => $v->errors()], 422);
        }

        $branch = Branch::create([
            ...$request->only('code', 'name_ar', 'name_en', 'country', 'city'),
            'created_by' => $request->user()->id,
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
            'name_ar'   => 'sometimes|string|max:100',
            'name_en'   => 'sometimes|string|max:100',
            'country'   => 'nullable|string|max:50',
            'city'      => 'nullable|string|max:50',
            'is_active' => 'nullable|boolean',
        ]);

        if ($v->fails()) {
            return response()->json(['success' => false, 'errors' => $v->errors()], 422);
        }

        $branch->update($request->only('name_ar', 'name_en', 'country', 'city', 'is_active'));
        ActivityLog::record('update_branch', $branch);

        return response()->json(['success' => true, 'data' => $branch]);
    }

    // DELETE /api/branches/{id}  (Finance Admin only)
    // Uses forceDelete() so the code can be reused immediately.
    // commission_cards.branch_id → set to NULL via DB trigger/FK nullOnDelete.
    public function destroy(Request $request, int $id): JsonResponse
    {
        if (!$request->user()->isFinanceAdmin()) {
            return response()->json(['success' => false, 'message' => 'Finance Admin only.'], 403);
        }

        $branch = Branch::withCount(['commissionCards', 'employees'])->findOrFail($id);
        $cardCount = $branch->commission_cards_count;
        $empCount  = $branch->employees_count;

        // Detach related records before hard delete
        // Set branch_id = NULL on commission cards (avoids FK violation)
        DB::table('commission_cards')->where('branch_id', $id)->update(['branch_id' => null]);
        // Set branch_id = NULL on employees
        DB::table('employees')->where('branch_id', $id)->update(['branch_id' => null]);
        // Set branch_id = NULL on users (managers)
        DB::table('users')->where('branch_id', $id)->update(['branch_id' => null]);

        ActivityLog::record('delete_branch', null, [
            'id'      => $id,
            'name_ar' => $branch->name_ar,
            'code'    => $branch->code,
        ]);

        // Hard delete — frees the code for reuse
        $branch->forceDelete();

        $details = [];
        if ($cardCount > 0) $details[] = "{$cardCount} حساب أصبح بدون فرع";
        if ($empCount  > 0) $details[] = "{$empCount} موظف أصبح بدون فرع";

        return response()->json([
            'success' => true,
            'message' => "✅ تم حذف الفرع \"{$branch->name_ar}\" نهائياً." .
                         (count($details) ? ' (' . implode(', ', $details) . ')' : ''),
        ]);
    }
}
