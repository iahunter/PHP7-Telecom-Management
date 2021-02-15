<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;

class Phone extends Model
{
    //
    use Auditable;
    use SoftDeletes;
    protected $table = 'phone';
    protected $fillable = ['phoneplan', 'site', 'name', 'device', 'firstname', 'lastname', 'username', 'dn', 'language', 'voicemail', 'vm_user_template', 'deployed', 'provisioned', 'assignments', 'system_id', 'notes', 'created_by', 'updated_by'];

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
        // Check if exceeds max of 255
        if (strlen($this->language) > 255) {
            throw new \Exception('status exceeded 255 characters');
        }
        // Check if exceeds max of 255
        if ($this->language) {
            $this->language = strtolower($this->language);
        }
    }
}
