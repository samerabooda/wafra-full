<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class CardModification extends Model {
    public $timestamps = false;
    protected $fillable = ['card_id','account_number','month','reason','notes','old_data','new_data','modified_by','modified_at'];
    protected $casts    = ['old_data'=>'array','new_data'=>'array','modified_at'=>'datetime'];
    public function card(): BelongsTo       { return $this->belongsTo(CommissionCard::class); }
    public function modifiedBy(): BelongsTo { return $this->belongsTo(User::class,'modified_by'); }
}
