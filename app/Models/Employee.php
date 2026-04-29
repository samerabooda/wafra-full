<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Employee extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'email', 'role', 'branch_id',
        'broker_commission', 'marketing_commission', 'cc_commission',
        'status', 'added_by', 'approved_by',
        'approved_at', 'rejected_reason',
        'is_base', 'is_active',
    ];

    protected $casts = [
        'broker_commission'    => 'decimal:2',
        'marketing_commission' => 'decimal:2',
        'cc_commission'        => 'decimal:2',
        'approved_at'          => 'datetime',
        'is_base'              => 'boolean',
        'is_active'            => 'boolean',
    ];

    public function branch(): BelongsTo     { return $this->belongsTo(Branch::class); }
    public function addedBy(): BelongsTo    { return $this->belongsTo(User::class, 'added_by'); }
    public function approvedBy(): BelongsTo { return $this->belongsTo(User::class, 'approved_by'); }

    public function scopeApproved($q)   { return $q->where('status', 'approved'); }
    public function scopePending($q)    { return $q->where('status', 'pending'); }
    public function scopeBrokers($q)    { return $q->where('role', 'broker'); }
    public function scopeMarketers($q)  { return $q->where('role', 'marketing'); }
    public function scopeExternal($q)   { return $q->where('role', 'external'); }
}
