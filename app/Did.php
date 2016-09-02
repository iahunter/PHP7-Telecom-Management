<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
// Add Softdeletes
use Illuminate\Database\Eloquent\SoftDeletes;
// Include Parent DID Block
use App\Didblock;

/*
* Create Did Model Here
*/

class Did extends Model
{
    //
    use SoftDeletes;
    protected $table = 'did';
    protected $fillable = ['name', 'number', 'status', 'system_id', 'assignments'];

    protected static function boot()
    {
        parent::boot();
        static::saving(function ($did) {
            return $did->validate();
        });
    }

    // Get the DID Block DID belongs to
    public function didblock()
    {
        return $this->belongsTo(Didblock::class);
    }

    public function isJson($string)
    {
        json_decode($string);

        return json_last_error() == JSON_ERROR_NONE;
    }

    protected function validate()
    {
        // Check if name exceeds max of 255
        if (strlen($this->name) > 255) {
            throw new \Exception('Name exceeded 255 characters');
        }
        // Make sure the start and end attributes are impossible to change once set
        if (isset($this->original['number']) && $this->original['number'] !== $this->start) {
            throw new \Exception('Validation error, Number can not be altered once created');
        }

        if (isset($this->assignments) && (! isJson($this->assignments))) {
            throw new \Exception('Validation error, assignement must be JSON');
        }
    }
}
