<?php

namespace App;

//use OwenIt\Auditing\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cucmsiteconfigs extends Model
{
    //
    //use Auditable;
    use SoftDeletes;
    protected $table = 'cucmsite';
    protected $fillable = ['sitecode', 'sitesummary', 'sitedetails', 'trunking', 'e911'];

    // Cast data type conversions. Converting one type of data to another.
    protected $casts = [
            'sitesummary'      => 'array',
            'sitedetails'      => 'array',
        ];

    protected static function boot()
    {
        parent::boot();
    }
}
