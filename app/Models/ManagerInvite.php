<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ManagerInvite extends Model
{
    protected $fillable = [
        'email', 'branch_id', 'role', 'note', 'invited_by', 'used_at',
    ];

    protected $casts = [
        'used_at' => 'datetime',
    ];

    // ── Relations ──────────────────────────────────────────────
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    // ── Helpers ────────────────────────────────────────────────
    public function isPending(): bool
    {
        return $this->used_at === null;
    }

    // ── Scopes ─────────────────────────────────────────────────
    public function scopePending($query)
    {
        return $query->whereNull('used_at');
    }

    public function scopeUsed($query)
    {
        return $query->whereNotNull('used_at');
    }
}
