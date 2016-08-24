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
    protected $fillable = ['didblock_id', 'name', 'number', 'status', 'system_id', 'assignments'];

	// Get the DID Block DID belongs to
    public function didblock()
    {
        return $this->belongsTo(Didblock::class);
    }
}
