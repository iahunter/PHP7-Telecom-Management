<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GatewayCalls extends Model
{
    protected $table = 'gateway_calls';
    protected $fillable = ['totalCalls', 'stats'];

    protected $casts = [
        'stats'    => 'array',
    ];
}
