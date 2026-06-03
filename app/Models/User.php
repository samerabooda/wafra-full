<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Notifications\ResetPasswordNotification;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, SoftDeletes;

    protected $fillable = [
        'name', 'email', 'phone', 'password', 'role',
        'branch_id', 'is_active', 'last_login_at',
        'must_change_pass', 'created_by',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at'     => 'datetime',
        'must_change_pass'  => 'boolean',
        'is_active'         => 'boolean',
        'password'          => 'hashed',
    ];

    // ── Relations ──────────────────────────────────────────────
    public function branch(): BelongsTo    { return $this->belongsTo(Branch::class); }
    public function permissions(): HasMany { return $this->hasMany(UserPermission::class); }

    // ── Helpers ────────────────────────────────────────────────
    public function isFinanceAdmin(): bool  { return $this->role === 'finance_admin'; }
    public function isBranchManager(): bool { return $this->role === 'branch_manager'; }

    /**
     * Returns true for ANY non-FA role that must be locked to their branch.
     * This covers both branch_manager AND viewer roles.
     * Only applies when branch_id is actually set (null = not locked).
     */
    public function isScopedToBranch(): bool
    {
        return in_array($this->role, ['branch_manager', 'viewer'])
            && $this->branch_id !== null;
    }

    /** True if this user belongs to a Call-Center branch (or is Finance Admin). */
    public function isCallCenterStaff(): bool
    {
        if ($this->isFinanceAdmin()) return true;
        if (!$this->branch_id) return false;
        if ($this->relationLoaded('branch')) return (bool) ($this->branch?->is_call_center);
        return (bool) \App\Models\Branch::whereKey($this->branch_id)->value('is_call_center');
    }

    /** True only if this user is the MANAGER of a Call-Center branch. */
    public function isCallCenterManager(): bool
    {
        return $this->isBranchManager() && $this->branch_id
            && (bool) \App\Models\Branch::whereKey($this->branch_id)->value('is_call_center');
    }

    // ── Password Reset ─────────────────────────────────────────
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    public function hasPermission(string $key): bool
    {
        if ($this->isFinanceAdmin()) return true;
        $perm = $this->permissions->firstWhere('permission', $key);
        return $perm ? (bool) $perm->granted : false;
    }

    public function allPermissions(): array
    {
        if ($this->isFinanceAdmin()) {
            return array_fill_keys([
                'dashboard', 'cards', 'modified', 'reports',
                'create_card', 'edit_card', 'employees',
                'import', 'export', 'branch_switch',
            ], true);
        }
        return $this->permissions->pluck('granted', 'permission')->toArray();
    }
}
