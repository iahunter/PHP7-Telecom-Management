<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Reports extends Model
{
    // Generic Reports Model used to store random data. Use category and type to store different types of report data.

    protected $table = 'reports';
    protected $fillable = ['parent', 'category', 'type', 'total', 'int0', 'int1', 'int2', 'int3', 'int4', 'stringfield0', 'stringfield1', 'stringfield2', 'stringfield3', 'stringfield4', 'custom', 'stats', 'json'];

    protected $casts = [
        'stats'    => 'array',
        'json'     => 'array',
    ];

    protected $attributes = [
        'json' => '{}',
    ];
}
