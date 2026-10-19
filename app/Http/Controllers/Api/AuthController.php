<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{User, UserPermission, ActivityLog, ManagerInvite};
use Illuminate\Support\Facades\DB;
use Illuminate\Http\{Request, JsonResponse};
use Illuminate\Support\Facades\{Hash, Validator};

class AuthController extends Controller
{
    // ── POST /api/auth/register ──────────────────────────────
    // Finance Admin first-time registration only
    public function register(Request $request): JsonResponse
    {
        if (User::where('role', 'finance_admin')->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Finance Admin account already exists. Please login.',
            ], 403);
        }

        $v = Validator::make($request->all(), [
            'name'                  => 'required|string|max:100',
            'email'                 => 'required|email|max:150|unique:users',
            'password'              => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|string',
        ]);

        if ($v->fails()) {
            return response()->json(['success' => false, 'errors' => $v->errors()], 422);
        }

        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'role'      => 'finance_admin',
            'is_active' => true,
        ]);

        ActivityLog::record('fa_registered', $user);

        return response()->json([
            'success' => true,
            'message' => 'Finance Admin account created successfully.',
            'user'    => $this->userResource($user),
            'token'   => $user->createToken('wafra-app')->plainTextToken,
        ], 201);
    }

    // ── POST /api/auth/login ─────────────────────────────────
    public function login(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if ($v->fails()) {
            return response()->json(['success' => false, 'errors' => $v->errors()], 422);
        }

        $user = User::with(['branch', 'permissions'])
                    ->where('email', $request->email)
                    ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email or password.',
            ], 401);
        }

        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Your account has been deactivated.',
            ], 403);
        }

        // Revoke old tokens (single session)
        $user->tokens()->delete();

        $user->update(['last_login_at' => now()]);
        ActivityLog::record('login', $user);

        return response()->json([
            'success'          => true,
            'user'             => $this->userResource($user),
            'permissions'      => $user->allPermissions(),
            'must_change_pass' => (bool) $user->must_change_pass,
            'token'            => $user->createToken('wafra-app')->plainTextToken,
        ]);
    }

    // ── POST /api/auth/logout ────────────────────────────────
    public function logout(Request $request): JsonResponse
    {
        ActivityLog::record('logout');
        $request->user()->currentAccessToken()->delete();

        return response()->json(['success' => true, 'message' => 'Logged out successfully.']);
    }

    // ── POST /api/auth/change-password ───────────────────────
    public function changePassword(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'password'         => 'required|string|min:8|confirmed',
        ]);

        if ($v->fails()) {
            return response()->json(['success' => false, 'errors' => $v->errors()], 422);
        }

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect.',
            ], 403);
        }

        $user->update([
            'password'         => Hash::make($request->password),
            'must_change_pass' => false,
        ]);

        ActivityLog::record('password_changed', $user);

        return response()->json(['success' => true, 'message' => 'Password changed successfully.']);
    }

    // ── GET /api/auth/me ─────────────────────────────────────
    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load(['branch', 'permissions']);

        return response()->json([
            'success'     => true,
            'user'        => $this->userResource($user),
            'permissions' => $user->allPermissions(),
        ]);
    }

    // ── POST /api/auth/check-invite ──────────────────────────
    // Public — checks if an email has a pending manager invite
    public function checkInvite(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($v->fails()) {
            return response()->json(['success' => false, 'message' => 'بريد إلكتروني غير صالح.'], 422);
        }

        // Check not already registered
        if (User::where('email', $request->email)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'هذا الإيميل مسجل مسبقاً. يرجى تسجيل الدخول.',
            ], 409);
        }

        $invite = ManagerInvite::where('email', $request->email)
                               ->whereNull('used_at')
                               ->with('branch')
                               ->first();

        if (! $invite) {
            return response()->json([
                'success' => false,
                'message' => 'هذا الإيميل غير مصرح بالتسجيل. تواصل مع المدير المالي.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'invite'  => [
                'id'     => $invite->id,
                'role'   => $invite->role,
                'branch' => $invite->branch ? $invite->branch->only('id', 'name_ar') : null,
                'note'   => $invite->note,
            ],
        ]);
    }

    // ── POST /api/auth/register-invite ───────────────────────
    // Public — branch manager self-registration via FA invite
    public function registerViaInvite(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'name'                  => 'required|string|max:100',
            'email'                 => 'required|email|max:150|unique:users,email',
            'password'              => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|string',
        ]);

        if ($v->fails()) {
            return response()->json(['success' => false, 'errors' => $v->errors()], 422);
        }

        $invite = ManagerInvite::where('email', $request->email)
                               ->whereNull('used_at')
                               ->first();

        if (! $invite) {
            return response()->json([
                'success' => false,
                'message' => 'الدعوة غير موجودة أو تم استخدامها مسبقاً.',
            ], 403);
        }

        $user = null;
        DB::transaction(function () use ($request, $invite, &$user) {
            $user = User::create([
                'name'             => $request->name,
                'email'            => $request->email,
                'password'         => Hash::make($request->password),
                'role'             => $invite->role,
                'branch_id'        => $invite->branch_id,
                'is_active'        => true,
                'must_change_pass' => false,
                'created_by'       => $invite->invited_by,
            ]);

            // Default permissions for branch manager
            $permissions = [
                'dashboard', 'cards', 'modified', 'reports',
                'create_card', 'edit_card', 'employees', 'import', 'export',
            ];
            foreach ($permissions as $perm) {
                UserPermission::create([
                    'user_id'    => $user->id,
                    'permission' => $perm,
                    'granted'    => true,
                ]);
            }

            // Consume the invite
            $invite->update(['used_at' => now()]);

            ActivityLog::record('manager_self_registered', $user, [
                'invite_id' => $invite->id,
                'branch_id' => $invite->branch_id,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء حسابك بنجاح. يمكنك الدخول الآن.',
        ], 201);
    }

    // ── Helpers ───────────────────────────────────────────────
    private function userResource(User $user): array
    {
        return [
            'id'               => $user->id,
            'name'             => $user->name,
            'email'            => $user->email,
            'role'             => $user->role,
            'branch_id'        => $user->branch_id,
            'branch'           => $user->branch?->only('id', 'code', 'name_ar', 'name_en'),
            'is_active'        => $user->is_active,
            'must_change_pass' => (bool) $user->must_change_pass,
            'last_login_at'    => $user->last_login_at?->toDateTimeString(),
        ];
    }
}
