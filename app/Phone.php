<?php

namespace App;

use OwenIt\Auditing\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Phone extends Model
{
    //
    use Auditable;
    use SoftDeletes;
    protected $table = 'phone';
    protected $fillable = ['parent', 'name', 'device', 'firstname', 'lastname', 'username', 'dn', 'language', 'voicemail', 'deployed', 'provisioned', 'assignments', 'system_id', 'notes'];

    // Cast data type conversions. Converting one type of data to another.
    protected $casts = [
            'assignments' => 'array',
        ];

    protected static function boot()
    {
        parent::boot();
        static::saving(function ($phone) {
            return $phone->validate();
        });
    }

    protected function validate()
    {
        // Check if exceeds max of 255
        if (strlen($this->name) > 255) {
            throw new \Exception('name exceeded 255 characters');
        }
        // Check if exceeds max of 255
        if (strlen($this->comment) > 255) {
            throw new \Exception('status exceeded 255 characters');
        }
        // Check if exceeds max of 255
        if (strlen($this->system_id) > 255) {
            throw new \Exception('system_id exceeded 255 characters');
        }
    }
}
