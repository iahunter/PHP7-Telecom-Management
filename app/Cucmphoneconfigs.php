<?php

namespace App;

//use OwenIt\Auditing\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cucmphoneconfigs extends Model
{
    //
    //use Auditable;
    use SoftDeletes;
    protected $table = 'cucmphone';
    protected $fillable = ['name', 'description', 'devicepool', 'css', 'model', 'ownerid', 'ipv4address', 'erl', 'risdb_ipv4address', 'risdb_registration_status', 'lines', 'config', 'last_registered'];

    // Cast data type conversions. Converting one type of data to another.
    protected $casts = [
            'lines'           => 'array',
            'config'          => 'array',
        ];

    protected static function boot()
    {
        parent::boot();
    }
}
