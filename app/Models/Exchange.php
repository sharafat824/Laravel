<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exchange extends Model
{
    //

    
    protected $fillable = [
        'name',
        'country',
        'exchange',
        'currency',
        'type',
        'isin',
    ];
}
