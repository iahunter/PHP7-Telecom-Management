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
    protected $table = 'did_block';
    protected $fillable = ['name', 'number', ''];
	
	// Get the DID Block DID belongs to
    public function didblock()
    {
        return $this->belongsTo(Didblock::class);
    }
}

/* Data Migration
            $table->increments('id');
            $table->integer('block_id')->unsigned();	// Parent Block ID
            $table->string('name');           			// Name
			$table->bigInteger('number');        		// Phone Number
			$table->string('status');            		// Status - Active/Reserved/Available
			$table->string('system_id');            	// Future - System ID - CUCM/Lync ID
			$table->json('assignements');           	// JSON Custom Field Data
            $table->timestamps();						// Time Stamps
            $table->softDeletes();            			// keep deactivated certificates in the table
			
*/