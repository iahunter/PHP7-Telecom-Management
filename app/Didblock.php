<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
// Add Softdeletes
use Illuminate\Database\Eloquent\SoftDeletes;

/* 
* Create Didblock Model Here
*/

class Didblock extends Model
{
    //
    use SoftDeletes;
    protected $table = 'did_block';
    protected $fillable = ['country_code', 'name', 'carrier', 'start', 'end'];

    // This overrides the parent boot function and adds
    // a complex custom validation handler for on-saving events
    protected static function boot() {
        parent::boot();
        static::saving(function($didblock) {
             return $didblock->validate();
        });
    }

    protected function validate()
    {
        if($this->country_code == 1) {
            echo 'Validation succeeded'.PHP_EOL;
            return true;
        } else {
            echo 'Validation failed'.PHP_EOL;
            return false;
        }
    }
}
