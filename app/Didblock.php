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
    protected $fillable = ['country_code', 'name', 'carrier', 'start', 'end', 'type', 'comment'];

    /* John trying help with update protection of start and end range numbers.
    public function getFillable()
    {
        if($this->id){
            return ['country_code', 'name', 'carrier', 'comment'];
        }
        return $this->fillable;
    }
    */

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
		// Make sure the start and end attributes are impossible to change once set
		if ( isset($this->original['start']) && $this->original['start'] !== $this->start) {
			throw new \Exception('Validation error, start range can not be altered once created');
		}
		if ( isset($this->original['end']) && $this->original['end'] !== $this->end) {
			throw new \Exception('Validation error, start range can not be altered once created');
		}
        // ADD VALIDATION THAT IS SPECIFIC TO THE
        // for updating use an if isset on start and end
        if ($this->country_code == 1) {
            return true;
        }
        if ($this->country_code == 2) {
            return true;
        } else {
            throw new \Exception('Invalid Country Code');
        }
    }

    protected function populate()
    {
        // Loop thru the range and create the individual DIDs in the block.
        $range = range($this->start, $this->end);
        foreach ($range as $number) {
            // Build the request for each number.
            $request = [
                        'name'   => '',
                        'number' => $number,
                        'status' => 'available',
                        ];

            // Create the dids inside block
            //$this->log($request);
            $response = $this->dids()->create($request); // This goes out and builds the new did transaction. The parent ID is joined automatically.
            //$this->log($response);
        }
    }
}
