<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class CommissionCard extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'account_number', 'month', 'month_date',
        'branch_id', 'account_type_id', 'account_status_id',
        'trading_type_id', 'account_kind',
        'cc_branch_id',      'cc_agent_id',          'cc_agent_commission',
        'cc_status',         'cc_rejection_reason',
        'broker_id',         'broker_commission',
        'marketer_id',       'marketer_commission',
        'ext_marketer1_id',  'ext_commission1',
        'ext_marketer2_id',  'ext_commission2',
        'forex_commission',  'futures_commission',
        'initial_deposit',   'monthly_deposit',
        'status', 'notes', 'import_batch_id', 'created_by',
    ];

    protected $casts = [
        'month_date'          => 'date',
        'broker_commission'   => 'decimal:2',
        'marketer_commission' => 'decimal:2',
        'ext_commission1'     => 'decimal:2',
        'ext_commission2'     => 'decimal:2',
        'forex_commission'    => 'decimal:2',
        'futures_commission'  => 'decimal:2',
        'initial_deposit'     => 'decimal:2',
        'monthly_deposit'     => 'decimal:2',
    ];

    // ── Relationships ──────────────────────────────────────────
    public function branch(): BelongsTo        { return $this->belongsTo(Branch::class); }
    public function ccBranch(): BelongsTo      { return $this->belongsTo(Branch::class, 'cc_branch_id'); }
    public function ccAgent(): BelongsTo       { return $this->belongsTo(Employee::class, 'cc_agent_id'); }
    public function scopeForCcBranch($q,int $id){ return $q->where('cc_branch_id',$id); }
    public function scopeCcPending($q)           { return $q->where('cc_status','cc_pending'); }
    public function scopeCcActive($q)            { return $q->whereNotNull('cc_branch_id')->where('cc_status','completed'); }
    public function accountType(): BelongsTo   { return $this->belongsTo(AccountType::class); }
    public function accountStatus(): BelongsTo { return $this->belongsTo(AccountStatus::class); }
    public function tradingType(): BelongsTo   { return $this->belongsTo(TradingType::class); }
    public function broker(): BelongsTo        { return $this->belongsTo(Employee::class, 'broker_id'); }
    public function marketer(): BelongsTo      { return $this->belongsTo(Employee::class, 'marketer_id'); }
    public function extMarketer1(): BelongsTo  { return $this->belongsTo(Employee::class, 'ext_marketer1_id'); }
    public function extMarketer2(): BelongsTo  { return $this->belongsTo(Employee::class, 'ext_marketer2_id'); }
    public function importBatch(): BelongsTo   { return $this->belongsTo(ImportBatch::class); }
    public function createdBy(): BelongsTo     { return $this->belongsTo(User::class, 'created_by'); }

    public function modifications(): HasMany
    {
        return $this->hasMany(CardModification::class, 'card_id')->latest('modified_at');
    }

    // ── Accessors ──────────────────────────────────────────────
    public function getTotalCommissionAttribute(): float
    {
        return (float)$this->broker_commission
             + (float)$this->marketer_commission
             + (float)$this->ext_commission1
             + (float)$this->ext_commission2;
    }

    // ── Scopes ─────────────────────────────────────────────────
    public function scopeModified($q)            { return $q->where('status', 'modified'); }
    public function scopeNewAdded($q)            { return $q->where('status', 'new_added'); }
    public function scopeForBranch($q, int $id)  { return $q->where('branch_id', $id); }
    public function scopeForMonth($q, string $m) { return $q->where('month', $m); }
    public function scopeForBroker($q, int $id)  { return $q->where('broker_id', $id); }

    public function scopeSearch($q, string $term)
    {
        return $q->where(function ($query) use ($term) {
            $query->where('account_number', 'like', "%{$term}%")
                  ->orWhere('month', 'like', "%{$term}%")
                  ->orWhereHas('broker',   fn($b) => $b->where('name', 'like', "%{$term}%"))
                  ->orWhereHas('marketer', fn($m) => $m->where('name', 'like', "%{$term}%"));
        });
    }

    public function scopeCcDraft($q)             { return $q->where('cc_status','cc_pending'); }
    public function scopeCcBranchPending($q)     { return $q->where('cc_status','branch_pending'); }
    public function scopeCcAwaitingBranch($q)    { return $q->whereIn('cc_status',['branch_pending','accepted']); }
    public function scopeCcCompleted($q)         { return $q->where('cc_status','completed'); }
}
