<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\{NewManagerMail, ManagerPasswordResetMail, ManagerInviteMail};
use App\Models\{User, UserPermission, Branch, ActivityLog, ManagerInvite};
use Illuminate\Http\{Request, JsonResponse};
use Illuminate\Support\Facades\{Hash, DB, Mail, Validator};
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
                            'phone'       => $u->phone,
                            'role'        => $u->role,
                            'is_active'   => $u->is_active,
                            'photo'       => $u->photo,
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

        // Free a soft-deleted manager's email so the same address can be reused.
        if ($email = trim((string)$request->email)) {
            $trashed = User::onlyTrashed()->where('email', $email)->first();
            if ($trashed) {
                $trashed->email = 'deleted_'.$trashed->id.'_'.time().'@deleted.invalid';
                $trashed->saveQuietly();
            }
        }

        $v = Validator::make($request->all(), [
            'name'          => 'required|string|max:100',
            'email'         => 'required|email|max:150|unique:users,email',
            'phone'         => 'nullable|string|max:30',
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
                'phone'            => $request->phone ?? null,
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

        // Send welcome email with credentials (silently fail — don't block account creation)
        $emailSent = false;
        try {
            $manager->load('branch');
            Mail::to($manager->email)->send(new NewManagerMail($manager, $plainPassword));
            $emailSent = true;
        } catch (\Throwable $e) {
            \Log::warning('NewManagerMail failed: ' . $e->getMessage());
        }

        return response()->json([
            'success'       => true,
            'message'       => "Manager account created for {$request->name}." . ($emailSent ? ' Email sent.' : ' (Email not sent — check mail config.)'),
            'email_sent'    => $emailSent,
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
            'phone'         => 'nullable|string|max:30',
            'branch_id'     => 'sometimes|required|exists:branches,id',   // required when present — cannot nullify
            'is_active'     => 'sometimes|boolean',
            'permissions'   => 'sometimes|array',
            'permissions.*' => 'string|in:dashboard,cards,modified,reports,create_card,edit_card,employees,import,export,branch_switch',
            'photo'         => 'sometimes|nullable|string',
        ]);

        if ($v->fails()) {
            return response()->json(['success' => false, 'errors' => $v->errors()], 422);
        }

        // Validate photo payload (data-URL, max ~2MB)
        if ($request->filled('photo')) {
            if (! preg_match('/^data:image\/(png|jpe?g|gif|webp);base64,/', $request->photo)) {
                return response()->json(['success'=>false,'message'=>'Invalid image format'], 422);
            }
            if (strlen($request->photo) > 2_800_000) {
                return response()->json(['success'=>false,'message'=>'Image too large (max ~2MB)'], 422);
            }
        }

        DB::transaction(function () use ($request, $manager) {
            $manager->update($request->only('name', 'phone', 'branch_id', 'is_active'));

            // Photo: set when provided, clear when explicitly sent empty/null
            if ($request->has('photo')) {
                $manager->photo = $request->filled('photo') ? $request->photo : null;
                $manager->save();
            }

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

        $origEmail = $manager->email;

        DB::transaction(function () use ($manager, $origEmail) {
            $manager->is_active = false;
            // Free the email slot (users.email has a UNIQUE index, and a soft-deleted
            // row keeps occupying it). Rename it so the same address can be re-invited
            // or re-registered later, while the row stays for the audit trail.
            $manager->email = 'deleted_'.$manager->id.'_'.time().'@deleted.invalid';
            $manager->save();
            $manager->tokens()->delete();
            $manager->delete(); // soft delete

            // Remove any invite still occupying this email (manager_invites.email is UNIQUE)
            \App\Models\ManagerInvite::where('email', $origEmail)->delete();
        });

        ActivityLog::record('delete_manager', $manager, ['freed_email' => $origEmail]);

        return response()->json([
            'success' => true,
            'message' => "Manager {$manager->name} deactivated. Email {$origEmail} freed for reuse.",
        ]);
    }

    // ══════════════════════════════════════════════════════════
    // INVITES — pre-registered emails for branch manager self-signup
    // ══════════════════════════════════════════════════════════

    // ── GET /api/manager-invites ──────────────────────────────
    public function invites(Request $request): JsonResponse
    {
        if ($err = $this->requireFA($request)) return $err;

        $invites = ManagerInvite::with('branch')
                                ->orderByDesc('created_at')
                                ->get()
                                ->map(fn($i) => [
                                    'id'         => $i->id,
                                    'email'      => $i->email,
                                    'branch'     => $i->branch?->only('id', 'code', 'name_ar', 'name_en'),
                                    'role'       => $i->role,
                                    'note'       => $i->note,
                                    'is_pending' => $i->isPending(),
                                    'used_at'    => $i->used_at?->toDateTimeString(),
                                    'created_at' => $i->created_at->toDateTimeString(),
                                ]);

        return response()->json(['success' => true, 'data' => $invites]);
    }

    // ── POST /api/manager-invites ─────────────────────────────
    public function storeInvite(Request $request): JsonResponse
    {
        if ($err = $this->requireFA($request)) return $err;

        // If the address belongs to a previously DELETED (soft-deleted) manager, free it
        // so the Finance Admin can legitimately re-invite the same email. Active users are
        // untouched (still blocked by the unique rule below). Also clears any stale invite
        // row occupying this email (manager_invites.email is UNIQUE).
        if ($email = trim((string)$request->email)) {
            $trashed = User::onlyTrashed()->where('email', $email)->first();
            if ($trashed) {
                $trashed->email = 'deleted_'.$trashed->id.'_'.time().'@deleted.invalid';
                $trashed->saveQuietly();
            }
            ManagerInvite::where('email', $email)->delete();
        }

        $v = Validator::make($request->all(), [
            'email'     => 'required|email|max:150|unique:manager_invites,email|unique:users,email',
            'branch_id' => 'nullable|exists:branches,id',
            'role'      => 'nullable|in:branch_manager,viewer',
            'note'      => 'nullable|string|max:255',
        ]);

        if ($v->fails()) {
            return response()->json(['success' => false, 'errors' => $v->errors()], 422);
        }

        $invite = ManagerInvite::create([
            'email'      => $request->email,
            'branch_id'  => $request->branch_id,
            'role'       => $request->role ?? 'branch_manager',
            'note'       => $request->note,
            'invited_by' => $request->user()->id,
        ]);

        ActivityLog::record('invite_created', null, [
            'email'     => $invite->email,
            'branch_id' => $invite->branch_id,
        ]);

        // Send invitation email to the manager
        $emailSent = false;
        try {
            $branchName = $invite->branch?->name_ar;
            Mail::to($invite->email)->send(new ManagerInviteMail($invite->email, $branchName, $invite->role));
            $emailSent = true;
        } catch (\Throwable $e) {
            \Log::warning('ManagerInviteMail failed: ' . $e->getMessage());
        }

        return response()->json([
            'success'    => true,
            'email_sent' => $emailSent,
            'message'    => 'تمت إضافة الإيميل للقائمة المسموح بها.' . ($emailSent ? ' تم إرسال بريد الدعوة.' : ' (البريد لم يُرسل.)'),
            'data'       => $invite->load('branch'),
        ], 201);
    }

    // ── DELETE /api/manager-invites/{id} ─────────────────────
    public function destroyInvite(Request $request, int $id): JsonResponse
    {
        if ($err = $this->requireFA($request)) return $err;

        $invite = ManagerInvite::findOrFail($id);

        if (! $invite->isPending()) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن حذف دعوة تم استخدامها.',
            ], 422);
        }

        $invite->delete();

        return response()->json(['success' => true, 'message' => 'تم حذف الدعوة.']);
    }

    // ── POST /api/managers/{id}/reset-password ────────────────
    public function resetPassword(Request $request, int $id): JsonResponse
    {
        if ($err = $this->requireFA($request)) return $err;

        // Scope to non-FA users only — prevents accidentally resetting another FA's password
        $manager = User::whereIn('role', ['branch_manager', 'viewer'])->findOrFail($id);
        $newPassword   = Str::upper(Str::random(4)) . '@' . rand(1000, 9999);

        $manager->update([
            'password'         => Hash::make($newPassword),
            'must_change_pass' => true,
        ]);

        $manager->tokens()->delete(); // Force re-login

        ActivityLog::record('reset_password', $manager);

        // Send email with new credentials (silently fail)
        $emailSent = false;
        try {
            Mail::to($manager->email)->send(new ManagerPasswordResetMail($manager, $newPassword));
            $emailSent = true;
        } catch (\Throwable $e) {
            \Log::warning('ManagerPasswordResetMail failed: ' . $e->getMessage());
        }

        return response()->json([
            'success'      => true,
            'message'      => "Password reset for {$manager->name}." . ($emailSent ? ' Email sent.' : ''),
            'email_sent'   => $emailSent,
            'new_password' => $newPassword,
        ]);
    }
}
