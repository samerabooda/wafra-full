<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{User, UserPermission, Branch, ActivityLog};
use Illuminate\Http\{Request, JsonResponse};
use Illuminate\Support\Facades\{Hash, DB, Validator};
use Illuminate\Support\Str;

class ManagerController extends Controller
{
    // All methods require Finance Admin
    private function requireFA(Request $request): ?JsonResponse
    {
        if (!$request->user()->isFinanceAdmin()) {
            return response()->json(['success' => false, 'message' => 'Finance Admin only.'], 403);
        }
        return null;
    }

    // ── GET /api/managers ─────────────────────────────────────
    public function index(Request $request): JsonResponse
    {
        if ($err = $this->requireFA($request)) return $err;

        $managers = User::with(['branch', 'permissions'])
                        ->whereIn('role', ['branch_manager', 'viewer'])
                        ->orderBy('name')
                        ->get()
                        ->map(fn($u) => [
                            'id'          => $u->id,
                            'name'        => $u->name,
                            'email'       => $u->email,
                            'role'        => $u->role,
                            'is_active'   => $u->is_active,
                            'branch'      => $u->branch?->only('id','code','name_ar','name_en'),
                            'permissions' => $u->allPermissions(),
                            'last_login'  => $u->last_login_at?->toDateTimeString(),
                        ]);

        return response()->json(['success' => true, 'data' => $managers]);
    }

    // ── POST /api/managers ────────────────────────────────────
    public function store(Request $request): JsonResponse
    {
        if ($err = $this->requireFA($request)) return $err;

        $v = Validator::make($request->all(), [
            'name'          => 'required|string|max:100',
            'email'         => 'required|email|max:150|unique:users,email',
            'branch_id'     => 'required|exists:branches,id',
            'role'          => 'nullable|in:branch_manager,viewer',
            'password'      => 'nullable|string|min:8',
            'permissions'   => 'nullable|array',
            'permissions.*' => 'string|in:dashboard,cards,modified,reports,create_card,edit_card,employees,import,export,branch_switch',
        ]);

        if ($v->fails()) {
            return response()->json(['success' => false, 'errors' => $v->errors()], 422);
        }

        $plainPassword = $request->password ?? (Str::upper(Str::random(4)) . '@' . rand(1000, 9999));

        $manager = null;
        DB::transaction(function () use ($request, $plainPassword, &$manager) {
            $manager = User::create([
                'name'             => $request->name,
                'email'            => $request->email,
                'password'         => Hash::make($plainPassword),
                'role'             => $request->role ?? 'branch_manager',
                'branch_id'        => $request->branch_id,
                'is_active'        => true,
                'must_change_pass' => true,
                'created_by'       => $request->user()->id,
            ]);

            // Save permissions
            $permissions = $request->permissions ?? [
                'dashboard', 'cards', 'modified', 'reports',
                'create_card', 'edit_card', 'employees', 'import', 'export',
            ];

            foreach ($permissions as $perm) {
                UserPermission::create([
                    'user_id'    => $manager->id,
                    'permission' => $perm,
                    'granted'    => true,
                ]);
            }

            ActivityLog::record('create_manager', $manager, [
                'branch_id'   => $request->branch_id,
                'permissions' => $permissions,
            ]);
        });

        $branch = Branch::find($request->branch_id);

        return response()->json([
            'success'       => true,
            'message'       => "Manager account created for {$request->name}.",
            'data'          => $manager->load(['branch', 'permissions']),
            'credentials'   => [
                'email'         => $manager->email,
                'temp_password' => $plainPassword,
                'branch'        => $branch?->name_ar,
                'note'          => 'User must change password on first login.',
            ],
        ], 201);
    }

    // ── PUT /api/managers/{id} ────────────────────────────────
    public function update(Request $request, int $id): JsonResponse
    {
        if ($err = $this->requireFA($request)) return $err;

        $manager = User::whereIn('role', ['branch_manager','viewer'])->findOrFail($id);

        $v = Validator::make($request->all(), [
            'name'          => 'sometimes|string|max:100',
            'branch_id'     => 'sometimes|exists:branches,id',
            'is_active'     => 'sometimes|boolean',
            'permissions'   => 'sometimes|array',
            'permissions.*' => 'string|in:dashboard,cards,modified,reports,create_card,edit_card,employees,import,export,branch_switch',
        ]);

        if ($v->fails()) {
            return response()->json(['success' => false, 'errors' => $v->errors()], 422);
        }

        DB::transaction(function () use ($request, $manager) {
            $manager->update($request->only('name', 'branch_id', 'is_active'));

            if ($request->has('permissions')) {
                // Replace all permissions
                UserPermission::where('user_id', $manager->id)->delete();
                foreach ($request->permissions as $perm) {
                    UserPermission::create([
                        'user_id'    => $manager->id,
                        'permission' => $perm,
                        'granted'    => true,
                    ]);
                }
            }

            ActivityLog::record('update_manager', $manager);
        });

        return response()->json([
            'success' => true,
            'message' => "Manager {$manager->name} updated.",
            'data'    => $manager->fresh(['branch', 'permissions']),
        ]);
    }

    // ── DELETE /api/managers/{id} ─────────────────────────────
    public function destroy(Request $request, int $id): JsonResponse
    {
        if ($err = $this->requireFA($request)) return $err;

        $manager = User::whereIn('role', ['branch_manager','viewer'])->findOrFail($id);

        // Soft delete
        $manager->update(['is_active' => false]);
        $manager->tokens()->delete();
        $manager->delete();

        ActivityLog::record('delete_manager', $manager);

        return response()->json([
            'success' => true,
            'message' => "Manager {$manager->name} deactivated.",
        ]);
    }

    // ── POST /api/managers/{id}/reset-password ────────────────
    public function resetPassword(Request $request, int $id): JsonResponse
    {
        if ($err = $this->requireFA($request)) return $err;

        $manager       = User::findOrFail($id);
        $newPassword   = Str::upper(Str::random(4)) . '@' . rand(1000, 9999);

        $manager->update([
            'password'         => Hash::make($newPassword),
            'must_change_pass' => true,
        ]);

        $manager->tokens()->delete(); // Force re-login

        ActivityLog::record('reset_password', $manager);

        return response()->json([
            'success'      => true,
            'message'      => "Password reset for {$manager->name}.",
            'new_password' => $newPassword,
        ]);
    }
}
