<?php

namespace App;

use OwenIt\Auditing\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cucmphoneconfigs extends Model
{
    //
    use Auditable;
    use SoftDeletes;
    protected $table = 'cucmsite';
    protected $fillable = ['sitecode', 'e911', 'trunking', 'config'];

    // Cast data type conversions. Converting one type of data to another.
    protected $casts = [
            'config'      => 'array',
        ];

    protected static function boot()
    {
        parent::boot();
    }
}
