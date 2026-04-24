<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, SoftDeletes;

    protected $fillable = [
        'name', 'email', 'password', 'role',
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
