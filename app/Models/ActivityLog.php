<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class ActivityLog extends Model {
    public $timestamps = false;
    protected $fillable = ['user_id','action','model_type','model_id','description','ip_address','user_agent','metadata','created_at'];
    protected $casts    = ['metadata'=>'array','created_at'=>'datetime'];
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public static function record(string $action, $model=null, array $meta=[]): self {
        return static::create([
            'user_id'    => auth()->id(),
            'action'     => $action,
            'model_type' => $model ? get_class($model) : null,
            'model_id'   => $model?->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'metadata'   => $meta,
            'created_at' => now(),
        ]);
    }
}
