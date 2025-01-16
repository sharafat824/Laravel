<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExchangePrice extends Model
{
    protected $fillable = [
        'adjusted_close',
        'close',
        'date',
        'high',
        'low',
        'open',
        'volume',
        'exchange_id'
    ];
    protected $casts = [
        'adjusted_close' => 'float',
        'close' => 'float',
        'high' => 'float',
        'low' => 'float',
        'open' => 'float',
        'volume' => 'float',
    ];
    protected $hidden = ['created_at', 'updated_at', 'exchange_id', 'id'];
}
