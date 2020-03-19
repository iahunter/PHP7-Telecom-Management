<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CucmPhoneStats extends Model
{
    protected $table = 'cucmphonestats';
    protected $fillable = ['type', 'total', 'registered', 'stats', 'json'];

    protected $casts = [
        'stats'    => 'array',
        'json'     => 'array',
    ];

    protected $attributes = [
        'json' => '{}',
    ];
}
