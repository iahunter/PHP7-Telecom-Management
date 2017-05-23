<?php

namespace App;

use OwenIt\Auditing\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TelecomInfrastructure extends Model
{
    use Auditable;
    use SoftDeletes;

    protected $table = 'telecom_infrastructure';
    protected $fillable = ['hostname',
                            'comment',
                            'role',
                            'manufacture',
                            'model',
                            'software_version',
                            'ip_address',
                            'mgmt_url',
                            'location',
                            'json',
                            'created_by',
                            'updated_by',
                            'deleted_by',
                        ];

    // Cast data type conversions. Converting one type of data to another.
    protected $casts = [
            'json' => 'array',
        ];
}
