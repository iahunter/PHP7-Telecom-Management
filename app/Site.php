<?php

namespace App;

use OwenIt\Auditing\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Site extends Model
{
    //
    use Auditable;
    use SoftDeletes;
    protected $table = 'site';
    protected $fillable = ['sitecode', 'type', 'srstip', 'h323ip', 'npa', 'nxx', 'timezone', 'operator', 'comment', 'didrange', 'didblocks', 'details'];

    // Cast data type conversions. Converting one type of data to another.
    protected $casts = [
            'h323ip'   => 'array',
            'didrange' => 'array',
			'didblocks' => 'array',
            'details'  => 'array',
        ];

    protected static function boot()
    {
        parent::boot();
        static::saving(function ($site) {
            return $site->validate();
        });
		
		// Cascade Soft Deletes Child Dids
        static::deleting(function ($site) {
            Phone::where('parent', $site->id)->delete();                // query did children of the didblock and delete them. Much faster than foreach!!!
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
