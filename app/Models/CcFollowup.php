<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One follow-up entry on a Call-Center lead — the per-lead call log.
 * Each branch → CC update appends a row here.
 *
 * outcome: in_progress | opened | declined | reassigned
 */
class CcFollowup extends Model
{
    protected $fillable = ['card_id', 'user_id', 'branch_id', 'outcome', 'note'];

    public function card(): BelongsTo   { return $this->belongsTo(CommissionCard::class, 'card_id'); }
    public function user(): BelongsTo   { return $this->belongsTo(User::class, 'user_id'); }
    public function branch(): BelongsTo { return $this->belongsTo(Branch::class, 'branch_id'); }
}
