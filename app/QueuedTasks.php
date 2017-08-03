<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QueuedTasks extends Model
{
	//use Auditable;
    use SoftDeletes;
    protected $table = 'queued_tasks';
    protected $fillable = ['form_data', 'json', 'status', 'system_id', 'created_by', 'updated_by', 'deleted_by'];


	// Cast data type conversions. Converting one type of data to another.
    protected $casts = [
            'form_data' => 'array',
			'json' => 'array',
        ];
}
