<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Calls extends Model
{
    //use Auditable;
    //use SoftDeletes;
    protected $table = 'sbc_calls';
    protected $fillable = ['name', 'totalCalls', 'stats'];

    protected $casts = [
        'stats'    => 'array',
    ];

    //protected $dateFormat = 'U';
}
