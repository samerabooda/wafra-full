<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

// ── Setting ───────────────────────────────────────────────
class Setting extends Model
{
    protected $fillable = ['key', 'value', 'type', 'description'];

    /**
     * Get a setting value with optional default.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = Cache::remember("setting_{$key}", 300, function () use ($key) {
            return static::where('key', $key)->first();
        });

        if (!$setting) return $default;

        return match ($setting->type) {
            'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
            'decimal' => (float) $setting->value,
            'integer' => (int) $setting->value,
            default   => $setting->value,
        };
    }

    /**
     * Set a setting value and bust cache.
     */
    public static function set(string $key, mixed $value, string $type = 'string'): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => (string) $value, 'type' => $type]
        );
        Cache::forget("setting_{$key}");
    }

    // ── Commission limit helpers ──────────────────────────
    public static function commissionLimitEnabled(): bool
    {
        return static::get('commission_limit_enabled', true);
    }

    public static function commissionLimitAmount(): float
    {
        return static::get('commission_limit_amount', 8.00);
    }

    public static function commissionWarningCount(): int
    {
        return static::get('commission_warning_count', 3);
    }
}


// ── CcNotification ────────────────────────────────────────
class CcNotification extends Model
{
    protected $fillable = [
        'card_id', 'from_branch_id', 'to_branch_id',
        'sent_by', 'responded_by',
        'type', 'status', 'message', 'read_at',
    ];

    protected $casts = ['read_at' => 'datetime'];

    public function card(): BelongsTo        { return $this->belongsTo(CommissionCard::class); }
    public function fromBranch(): BelongsTo  { return $this->belongsTo(Branch::class, 'from_branch_id'); }
    public function toBranch(): BelongsTo    { return $this->belongsTo(Branch::class, 'to_branch_id'); }
    public function sentBy(): BelongsTo      { return $this->belongsTo(User::class, 'sent_by'); }
    public function respondedBy(): BelongsTo { return $this->belongsTo(User::class, 'responded_by'); }

    public function scopeUnread($q)          { return $q->where('status', 'unread'); }
    public function scopeForBranch($q, int $id) { return $q->where('to_branch_id', $id); }

    /**
     * Mark as read.
     */
    public function markRead(): void
    {
        $this->update(['status' => 'read', 'read_at' => now()]);
    }
}
