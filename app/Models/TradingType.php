<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class TradingType extends Model {
    protected $fillable = ['name_en','name_ar','is_active','sort_order'];
    protected $casts    = ['is_active' => 'boolean'];
}
