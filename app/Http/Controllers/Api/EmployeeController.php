<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{Employee, ActivityLog, CommissionCard};
use Illuminate\Http\{Request, JsonResponse};
use Illuminate\Support\Facades\{Validator, DB};

class EmployeeController extends Controller
{
    // ── GET /api/employees ────────────────────────────────────
    public function index(Request $request): JsonResponse
    {
        $user  = $request->user();
        $slim  = $request->boolean('slim');   // dropdown payload: id/name/role/branch only, no relations
        // all_branches: card broker/marketer pickers may attribute a card to a broker from ANOTHER
        // branch — so the slim picker returns every approved employee (labelled with their branch).
        $allBranches = $request->boolean('all_branches');
        $query = $slim
            ? Employee::query()->select('id', 'name', 'role', 'branch_id', 'status')->with('branch:id,name_ar,name_en')
            : Employee::with(['branch', 'addedBy', 'approvedBy']);

        if ($user->isScopedToBranch() && !($slim && $allBranches)) {
            // Branch managers / viewers see only their own branch's approved employees
            $query->where('branch_id', $user->branchScopeId())
                  ->where('status', 'approved');
        } elseif ($slim && $allBranches) {
            // cross-branch picker → all APPROVED employees regardless of branch
            $query->where('status', 'approved');
        }

        // recent=1 → only employees who actually appear on commission cards in the last 2 years.
        if ($request->boolean('recent')) {
            $cutoff = now()->subYears(2)->startOfMonth()->toDateString();
            $base = \Illuminate\Support\Facades\DB::table('commission_cards')
                ->whereNull('deleted_at')->whereDate('month_date', '>=', $cutoff);
            $ids = collect();
            foreach (['broker_id', 'marketer_id', 'ext_marketer1_id', 'ext_marketer2_id'] as $col) {
                $ids = $ids->merge((clone $base)->whereNotNull($col)->distinct()->pluck($col));
            }
            $query->whereIn('id', $ids->unique()->values()->all() ?: [0]);
        }

        if ($status = $request->status) $query->where('status', $status);
        if ($role   = $request->role)   $query->where('role', $role);
        if ($request->branch_id && $user->isFinanceAdmin()) {
            $query->where('branch_id', $request->branch_id);
        }

        // Pending count: branch managers see only THEIR branch's pending; FA sees all.
        $pendingCount = 0;
        if (!$slim) {
            $pq = Employee::where('status', 'pending');
            if ($user->isScopedToBranch()) $pq->where('branch_id', $user->branchScopeId());
            $pendingCount = $pq->count();
        }

        return response()->json([
            'success'       => true,
            'data'          => $query->orderBy('name')->get(),
            'pending_count' => $pendingCount,
        ]);
    }

    // ── GET /api/employees/{id} ───────────────────────────────
    public function show(Request $request, int $id): JsonResponse
    {
        $emp  = Employee::with(['branch', 'addedBy', 'approvedBy'])->findOrFail($id);
        $user = $request->user();
        // Branch managers/viewers may only read employees of their own branch.
        if ($user->isScopedToBranch() && (int) $emp->branch_id !== $user->branchScopeId()) {
            return response()->json(['success' => false, 'message' => 'ليس لديك صلاحية على هذا الموظف.'], 403);
        }
        return response()->json(['success' => true, 'data' => $emp]);
    }

    // ── POST /api/employees ───────────────────────────────────
    public function store(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'name'                 => 'required|string|max:100',
            'email'                => 'nullable|email|max:150|unique:employees,email',
            'role'                 => 'required|in:broker,marketing,broker_marketer,external,other',
            'branch_id'            => 'nullable|exists:branches,id',
            'broker_commission'    => 'nullable|numeric|min:0',
            'marketing_commission' => 'nullable|numeric|min:0',
        ]);

        if ($v->fails()) {
            return response()->json(['success' => false, 'errors' => $v->errors()], 422);
        }

        $user     = $request->user();
        $isPending = $user->isBranchManager(); // FA adds directly, BM adds as pending

        $emp = Employee::create([
            'name'                 => $request->name,
            'email'                => $request->email,
            'role'                 => $request->role,
            // Branch managers can ONLY add to their own branch — never trust a posted branch_id.
            'branch_id'            => $user->isBranchManager() ? $user->branch_id : ($request->branch_id ?? $user->branch_id),
            'broker_commission'    => $request->broker_commission    ?? 0,
            'marketing_commission' => $request->marketing_commission ?? 0,
            'status'               => $isPending ? 'pending' : 'approved',
            'added_by'             => $user->id,
            'approved_by'          => $isPending ? null : $user->id,
            'approved_at'          => $isPending ? null : now(),
        ]);

        ActivityLog::record('create_employee', $emp, [
            'status' => $emp->status,
            'added_by_role' => $user->role,
        ]);

        return response()->json([
            'success' => true,
            'pending' => $isPending,
            'message' => $isPending
                ? "Employee {$emp->name} added. Awaiting Finance Admin approval."
                : "Employee {$emp->name} created and approved.",
            'data' => $emp->load('branch'),
        ], 201);
    }

    // ── PUT /api/employees/{id} ───────────────────────────────
    public function update(Request $request, int $id): JsonResponse
    {
        // Branch managers can only ADD employees (as pending) — editing/deleting is Finance Admin only.
        if (!$request->user()->isFinanceAdmin()) {
            return response()->json(['success' => false, 'message' => 'Finance Admin only.'], 403);
        }

        $emp = Employee::findOrFail($id);

        $v = Validator::make($request->all(), [
            'name'                 => 'sometimes|string|max:100',
            'email'                => "sometimes|nullable|email|unique:employees,email,{$id}",
            'role'                 => 'sometimes|in:broker,marketing,broker_marketer,external,other',
            'branch_id'            => 'nullable|exists:branches,id',
            'broker_commission'    => 'nullable|numeric|min:0',
            'marketing_commission' => 'nullable|numeric|min:0',
            'is_active'            => 'nullable|boolean',
        ]);

        if ($v->fails()) {
            return response()->json(['success' => false, 'errors' => $v->errors()], 422);
        }

        $emp->update($request->only([
            'name', 'email', 'role', 'branch_id',
            'broker_commission', 'marketing_commission', 'is_active',
        ]));

        ActivityLog::record('update_employee', $emp);

        return response()->json([
            'success' => true,
            'message' => "Employee {$emp->name} updated.",
            'data'    => $emp->fresh('branch'),
        ]);
    }

    // ── PUT /api/employees/{id}/approve  (Finance Admin only) ─
    public function approve(Request $request, int $id): JsonResponse
    {
        if (!$request->user()->isFinanceAdmin()) {
            return response()->json(['success' => false, 'message' => 'Finance Admin only.'], 403);
        }

        $emp = Employee::where('status', 'pending')->findOrFail($id);

        $emp->update([
            'status'      => 'approved',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);

        ActivityLog::record('approve_employee', $emp);

        return response()->json([
            'success' => true,
            'message' => "Employee {$emp->name} has been approved.",
            'data'    => $emp->fresh(['branch', 'approvedBy']),
        ]);
    }

    // ── PUT /api/employees/{id}/reject  (Finance Admin only) ──
    public function reject(Request $request, int $id): JsonResponse
    {
        if (!$request->user()->isFinanceAdmin()) {
            return response()->json(['success' => false, 'message' => 'Finance Admin only.'], 403);
        }

        $v = Validator::make($request->all(), [
            'reason' => 'nullable|string|max:500',
        ]);

        $emp = Employee::where('status', 'pending')->findOrFail($id);
        $emp->update([
            'status'          => 'rejected',
            'rejected_reason' => $request->reason ?? 'No reason provided',
        ]);

        ActivityLog::record('reject_employee', $emp, ['reason' => $request->reason]);

        return response()->json([
            'success' => true,
            'message' => "Employee {$emp->name} has been rejected.",
        ]);
    }

    // ── DELETE /api/employees/{id} ────────────────────────────
    public function destroy(Request $request, int $id): JsonResponse
    {
        // Only Finance Admin can delete employees
        if (!$request->user()->isFinanceAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Finance Admin only.',
            ], 403);
        }

        $emp = Employee::findOrFail($id);

        $name = $emp->name;

        // Before hard-deleting: any card where this employee is the broker,
        // marketer, or an external marketer must fall back to "Self" (id = NULL)
        // with a 0 commission, so no card is left pointing at a deleted person
        // and earning a commission.
        $asBroker   = CommissionCard::where('broker_id', $id)->count();
        $asMarketer = CommissionCard::where('marketer_id', $id)->count();
        $asExt1     = CommissionCard::where('ext_marketer1_id', $id)->count();
        $asExt2     = CommissionCard::where('ext_marketer2_id', $id)->count();

        DB::transaction(function () use ($id, $name, $emp, $asBroker, $asMarketer, $asExt1, $asExt2) {
            if ($asBroker) {
                CommissionCard::where('broker_id', $id)
                    ->update(['broker_id' => null, 'broker_commission' => 0]);
            }
            if ($asMarketer) {
                CommissionCard::where('marketer_id', $id)
                    ->update(['marketer_id' => null, 'marketer_commission' => 0]);
            }
            if ($asExt1) {
                CommissionCard::where('ext_marketer1_id', $id)
                    ->update(['ext_marketer1_id' => null, 'ext_commission1' => 0]);
            }
            if ($asExt2) {
                CommissionCard::where('ext_marketer2_id', $id)
                    ->update(['ext_marketer2_id' => null, 'ext_commission2' => 0]);
            }

            ActivityLog::record('delete_employee', $emp, [
                'name'                 => $name,
                'cards_broker_reset'   => $asBroker,
                'cards_marketer_reset' => $asMarketer,
                'cards_ext1_reset'     => $asExt1,
                'cards_ext2_reset'     => $asExt2,
            ]);
            $emp->forceDelete(); // Hard delete to free name for reuse
        });

        $reset = $asBroker + $asMarketer + $asExt1 + $asExt2;
        $note  = $reset
            ? " (تم تحويل {$reset} حساب إلى Self وتصفير عمولته)"
            : '';

        return response()->json([
            'success' => true,
            'message' => "✅ تم حذف الموظف \"{$name}\" بنجاح.{$note}",
        ]);
    }

    // ── GET /api/employees/pending (Finance Admin only) ───────
    public function pending(Request $request): JsonResponse
    {
        if (!$request->user()->isFinanceAdmin()) {
            return response()->json(['success' => false, 'message' => 'Finance Admin only.'], 403);
        }

        $pending = Employee::with(['branch', 'addedBy'])
                           ->where('status', 'pending')
                           ->orderBy('created_at')
                           ->get();

        return response()->json([
            'success' => true,
            'count'   => $pending->count(),
            'data'    => $pending,
        ]);
    }
}
