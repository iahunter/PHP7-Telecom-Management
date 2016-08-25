<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
// Add Softdeletes
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Did;

/*
* Create Didblock Model Here
*/

class Didblock extends Model
{
    //
    use SoftDeletes;
    protected $table = 'did_block';
    protected $fillable = ['country_code', 'name', 'carrier', 'start', 'end', 'comment'];

    public function log($message = '')
    {
        if ($message) {
            // $this->messages[] = $message;
            file_put_contents(storage_path('logs/didblock.log'),
                                \Metaclassing\Utility::dumperToString($message).PHP_EOL,
                                FILE_APPEND | LOCK_EX
                            );
        }

        return $this->messages;
    }

    // This overrides the parent boot function and adds
    // a complex custom validation handler for on-saving events
    protected static function boot()
    {
        parent::boot();
        static::saving(function ($didblock) {
            return $didblock->validate();
        });
        static::created(function ($didblock) {
            return $didblock->populate();
        });
    }

    public function dids()
    {
        return $this->hasMany(Did::class);
    }

    protected function validate()
    {
        if ($this->country_code == 1) {
            //            echo 'Validation succeeded'.PHP_EOL;
            return true;
        }
        if ($this->country_code == 2) {
            //            echo 'Validation succeeded'.PHP_EOL;
            return true;
        } else {
            //            echo 'Validation failed'.PHP_EOL;
            return false;
        }
    }

    protected function populate()
    {
        // Loop thru the range and create the individual DIDs in the block.
        $range = range($this->start, $this->end);
        foreach ($range as $number) {
            $request = [
                        'name'   => '',
                        'number' => $number,
                        'status' => 'available',
                        ];
            // Create the dids inside
//			$this->log($request);
            $response = $this->dids()->create($request);
//			$this->log($response);
        }
    }
}
