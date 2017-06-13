<?php

namespace App;

use OwenIt\Auditing\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SiteMigration extends Model
{

    use Auditable;
    use SoftDeletes;
    protected $table = 'site_migration';
    protected $fillable = ['sitecode', 'comment', 'trunking', 'e911', 'srstip', 'h323ip', 'comment', 'backups', 'created_by', 'updated_by', 'deleted_by'];

    // Cast data type conversions. Converting one type of data to another.
    protected $casts = [
            'h323ip'      => 'array',
            'backups'    => 'array',
        ];
}
