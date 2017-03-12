<?php

namespace App;

use OwenIt\Auditing\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Phoneplan extends Model
{
    //
    use Auditable;
    use SoftDeletes;
    protected $table = 'phoneplan';
    protected $fillable = ['site', 'name', 'description', 'status', 'system_id', 'notes', 'language', 'employee_vm_user_template', 'nonemployee_vm_user_template', 'json', 'created_by', 'updated_by'];

    // Cast data type conversions. Converting one type of data to another.
    protected $casts = [
            'json' => 'array',
        ];

    protected static function boot()
    {
        parent::boot();
        static::saving(function ($phoneplan) {
            return $phoneplan->validate();
        });

        // Cascade Soft Deletes Child Dids
        static::deleting(function ($phoneplan) {
            Phone::where('phoneplan', $phoneplan->id)->delete();                // query phone children of the and delete them. Much faster than foreach!!!
        });
    }

    protected function validate()
    {
        // Check if exceeds max of 255
        if (strlen($this->name) > 255) {
            throw new \Exception('name exceeded 255 characters');
        }
        // Check if exceeds max of 255
        if (strlen($this->description) > 255) {
            throw new \Exception('status exceeded 255 characters');
        }
        // Check if exceeds max of 255
        if (strlen($this->system_id) > 255) {
            throw new \Exception('system_id exceeded 255 characters');
        }
    }
}
