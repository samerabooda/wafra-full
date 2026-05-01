<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

// ══════════════════════════════════════════════════════════════
// Branch
// ══════════════════════════════════════════════════════════════
class Branch extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code', 'name_ar', 'name_en', 'country', 'city',
        'is_active', 'created_by',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function users(): HasMany           { return $this->hasMany(User::class); }
    public function employees(): HasMany       { return $this->hasMany(Employee::class); }
    public function commissionCards(): HasMany { return $this->hasMany(CommissionCard::class); }
    public function createdBy(): BelongsTo     { return $this->belongsTo(User::class, 'created_by'); }

    // ── Scope: only active branches ─────────────────────────
    public function scopeActive($query) { return $query->where('is_active', true); }
}
