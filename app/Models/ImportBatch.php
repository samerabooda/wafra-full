<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};
class ImportBatch extends Model {
    protected $fillable = ['batch_code','filename','total_rows','imported_rows','failed_rows','status','error_log','imported_by','started_at','finished_at'];
    protected $casts    = ['error_log'=>'array','started_at'=>'datetime','finished_at'=>'datetime'];
    public function importedBy(): BelongsTo    { return $this->belongsTo(User::class,'imported_by'); }
    public function commissionCards(): HasMany { return $this->hasMany(CommissionCard::class,'import_batch_id'); }
}
