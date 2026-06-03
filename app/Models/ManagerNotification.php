<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ManagerNotification extends Model
{
    protected $fillable = [
        'type', 'card_id', 'account_number', 'month',
        'from_user_id', 'from_branch_id', 'to_user_id', 'to_branch_id',
        'title', 'body', 'data', 'delivered_at', 'read_at', 'acted_at', 'action',
    ];

    protected $casts = [
        'data'         => 'array',
        'delivered_at' => 'datetime',
        'read_at'      => 'datetime',
        'acted_at'     => 'datetime',
    ];

    public function card(): BelongsTo     { return $this->belongsTo(CommissionCard::class, 'card_id'); }
    public function fromUser(): BelongsTo  { return $this->belongsTo(User::class, 'from_user_id'); }
    public function toUser(): BelongsTo    { return $this->belongsTo(User::class, 'to_user_id'); }
    public function toBranch(): BelongsTo  { return $this->belongsTo(Branch::class, 'to_branch_id'); }
    public function fromBranch(): BelongsTo { return $this->belongsTo(Branch::class, 'from_branch_id'); }

    public function scopeUnread($q)  { return $q->whereNull('read_at'); }

    /**
     * Fan-out helper: create one notification per recipient user.
     * @param \Illuminate\Support\Collection|array $users  recipient User models
     */
    public static function fanOut($users, array $attrs): int
    {
        $now = now();
        $rows = [];
        foreach ($users as $u) {
            $rows[] = array_merge([
                'type'         => 'cc_new_card',
                'to_user_id'   => $u->id,
                'created_at'   => $now,
                'updated_at'   => $now,
            ], $attrs, [
                // ensure JSON column is encoded for bulk insert
                'data' => isset($attrs['data']) ? json_encode($attrs['data']) : null,
            ]);
        }
        if ($rows) self::insert($rows);
        return count($rows);
    }
}
