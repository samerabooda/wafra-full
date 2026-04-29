<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CcNotification extends Model
{
    protected $fillable = ['card_id','from_branch_id','to_branch_id','sent_by','responded_by','type','status','message','read_at'];
    protected $casts = ['read_at'=>'datetime'];

    public function card(): BelongsTo        { return $this->belongsTo(CommissionCard::class); }
    public function fromBranch(): BelongsTo  { return $this->belongsTo(Branch::class,'from_branch_id'); }
    public function toBranch(): BelongsTo    { return $this->belongsTo(Branch::class,'to_branch_id'); }
    public function sentBy(): BelongsTo      { return $this->belongsTo(User::class,'sent_by'); }
    public function respondedBy(): BelongsTo { return $this->belongsTo(User::class,'responded_by'); }

    public function scopeUnread($q)             { return $q->where('status','unread'); }
    public function scopeForBranch($q, int $id) { return $q->where('to_branch_id', $id); }
    public function markRead(): void            { $this->update(['status'=>'read','read_at'=>now()]); }
}
