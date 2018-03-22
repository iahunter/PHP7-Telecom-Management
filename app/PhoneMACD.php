<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PhoneMACD extends Model
{
    //use Auditable;
    use SoftDeletes;
    protected $table = 'phone_mac';
    protected $fillable = ['type', 'parent', 'form_data', 'json', 'status', 'created_by', 'updated_by', 'deleted_by'];

    // Cast data type conversions. Converting one type of data to another.
    protected $casts = [
            'form_data' => 'array',
            'json'      => 'array',
        ];

    protected static function boot()
    {
        parent::boot();

        // Cascade Soft Deletes Child Dids
        static::deleting(function ($macd) {
            PhoneMACD::where('parent', $macd->id)->delete();                // query did children of the didblock and delete them. Much faster than foreach!!!
        });
    }
	
	// Try to get status of all the children and return the job status of the worst status. 
	public static function get_parent_status($id)
    {

		$children = PhoneMACD::where('parent', $id)
					->get();
		
		$statuss = []; 
		
		foreach($children as $child){
			// Get all the children status's
			$statuss[] = $child['status']; 
		}
		
		// Count the number each status shows up. 
		$statuss = array_count_values($statuss); 		
		//return $statuss;
		
		if(in_array('error', $statuss)){
			$status = 'error'; 
		}
		elseif(in_array('entered queue', $statuss)){
			$status = 'entered queue'; 
		}
		elseif(in_array('job recieved', $statuss)){
			$status = 'job recieved'; 
		}
		else{
			// Get the value that appears most if none of the previous status's exist. 
			$status = array_search(max($statuss),$statuss); 
		}
		
		return $status; 
    }
}
