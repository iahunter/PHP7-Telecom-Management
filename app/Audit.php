<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
//use Illuminate\Database\Eloquent\SoftDeletes;

class Audit extends Model
{
    //
    //use SoftDeletes;
    protected $table = 'audit';
    protected $fillable = ['file', 'method', 'message', 'previous'];

	// Cast data type conversions. Converting one type of data to another. 
	protected $casts = [
			'previous' => 'array',
		];
}
