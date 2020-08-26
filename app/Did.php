<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
// Add Softdeletes
use Illuminate\Database\Eloquent\SoftDeletes;
// Include Parent DID Block
use OwenIt\Auditing\Auditable;

/*
* Create Did Model Here
*/

class Did extends Model
{
    //
    use Auditable;
    use SoftDeletes;
    protected $table = 'did';
    protected $fillable = ['name', 'number', 'status', 'system_id', 'created_by', 'updated_by', 'deleted_by'];

    // Cast data type conversions. Converting one type of data to another.
    protected $casts = [
        'assignments'  => 'array',
        'system_id'    => 'array',
        'mailbox'      => 'array',
    ];

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

    public static function get_first_available_did_by_sitecode($sitecode)
    {
        $didblocks = Didblock::where('name', 'like', "%{$sitecode}%")
                ->where('type', '=', 'public')
                ->where(function ($query) {
                    $query->where('reserved', '=', null)
                              ->orWhere('reserved', '=', 0);
                })

                ->orderBy('start')
                ->get();

        //return $didblocks;

        $show = [];
        foreach ($didblocks as $didblock) {
            $did = \App\Did::where('parent', $didblock->id)
                    ->where('status', 'available')
                    ->first();

            if($did){
				return $did;
			}
        }
    }

    protected function validate()
    {
        // Check if exceeds max of 255
        if (strlen($this->name) > 255) {
            throw new \Exception('name exceeded 255 characters');
        }
        // Check if exceeds max of 255
        if (strlen($this->status) > 255) {
            throw new \Exception('status exceeded 255 characters');
        }
        // Check if exceeds max of 255
        /*
        if (strlen($this->system_id) > 255) {
            throw new \Exception('system_id exceeded 255 characters');
        }
        */
        // Make sure the number attributes are impossible to change once set
        if (isset($this->original['number']) && $this->original['number'] !== $this->number) {
            throw new \Exception('Validation error, Number can not be altered once created');
        }
    }
}
