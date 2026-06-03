<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CardSummary extends Model
{
    protected $guarded = [];

    protected $casts = [
        'month_date'          => 'date',
        'total_initial'       => 'decimal:2',
        'total_monthly'       => 'decimal:2',
        'total_broker_comm'   => 'decimal:2',
        'total_marketer_comm' => 'decimal:2',
        'total_ext_comm'      => 'decimal:2',
        'rebuilt_at'          => 'datetime',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
