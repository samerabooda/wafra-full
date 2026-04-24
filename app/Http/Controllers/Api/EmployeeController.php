<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{Employee, ActivityLog};
use Illuminate\Http\{Request, JsonResponse};
use Illuminate\Support\Facades\Validator;

class EmployeeController extends Controller
{
    // ── GET /api/employees ────────────────────────────────────
    public function index(Request $request): JsonResponse
    {
        $user  = $request->user();
        $query = Employee::with(['branch', 'addedBy', 'approvedBy']);

        if ($user->isBranchManager()) {
            // Branch managers see only their branch approved employees
            $query->where('branch_id', $user->branch_id)
                  ->where('status', 'approved');
        }

        if ($status = $request->status) $query->where('status', $status);
        if ($role   = $request->role)   $query->where('role', $role);
        if ($branch = $request->branch_id && $user->isFinanceAdmin()) {
            $query->where('branch_id', $request->branch_id);
        }

        return response()->json([
            'success'       => true,
            'data'          => $query->orderBy('name')->get(),
            'pending_count' => Employee::where('status', 'pending')->count(),
        ]);
    }

    // ── GET /api/employees/{id} ───────────────────────────────
    public function show(int $id): JsonResponse
    {
        $emp = Employee::with(['branch', 'addedBy', 'approvedBy'])->findOrFail($id);
        return response()->json(['success' => true, 'data' => $emp]);
    }

    // ── POST /api/employees ───────────────────────────────────
    public function store(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'name'                 => 'required|string|max:100',
            'email'                => 'nullable|email|max:150|unique:employees,email',
            'role'                 => 'required|in:broker,marketing,external,other',
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
            'branch_id'            => $request->branch_id ?? $user->branch_id,
            'broker_commission'    => $request->broker_commission    ?? 4.00,
            'marketing_commission' => $request->marketing_commission ?? 3.00,
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
        $emp = Employee::findOrFail($id);

        $v = Validator::make($request->all(), [
            'name'                 => 'sometimes|string|max:100',
            'email'                => "sometimes|nullable|email|unique:employees,email,{$id}",
            'role'                 => 'sometimes|in:broker,marketing,external,other',
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
        $emp = Employee::findOrFail($id);

        if ($emp->is_base) {
            return response()->json([
                'success' => false,
                'message' => 'Base employees cannot be deleted.',
            ], 403);
        }

        $name = $emp->name;
        $emp->delete();
        ActivityLog::record('delete_employee', $emp);

        return response()->json([
            'success' => true,
            'message' => "Employee {$name} deleted.",
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
