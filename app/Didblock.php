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
        if (isset($this->original['start']) && $this->original['start'] !== $this->start) {
            throw new \Exception('Validation error, start range can not be altered once created');
        }
        if (isset($this->original['end']) && $this->original['end'] !== $this->end) {
            throw new \Exception('Validation error, end range can not be altered once created');
        }

        if (! preg_match('/^[0-9]+$/', $this->country_code)) {
            throw new \Exception('Country Code must be numeric');
        }

        if (! $this->name) {
            throw new \Exception('No Name Set');
        }

        /*
        // Check if Name is set
        if(!$this->country_code){
            if (empty($this->original['name']) || $this->original['name'] == '') {
                throw new \Exception('No Name Set');
            }
        }

        /*
        // Check if start is set
        if (empty($this->original['start']) || $this->original['start'] == '') {
            throw new \Exception('No Range Start Set');
        }
        // Check if end is set
        if (empty($this->original['end']) || $this->original['end'] == '') {

            // If there is no range end then create a single entry. - This is good for POTS lines/single number ranges.
            $this->original['end'] = $this->original['start'];
            //throw new \Exception('No Range End Set');
        }

        // Check if start are numbers.
        if (! preg_match('/^[0-9]+$/', $this->original['start'])) {
            throw new \Exception('Range start must be numeric');
        }
        // Check if end are numbers.
        if (! preg_match('/^[0-9]+$/', $this->original['end'])) {
            throw new \Exception('Range start must be numeric');
        }

        // Check to make sure start is not greater than end.
        if ($this->original['start'] > $this->original['end']) {
            throw new \Exception('Error: Range start must not be greater than range end');
        }

        // Check if start and end are in same NPA NXX if they have country Code of 1.
        if (($this->original['country_code'] == 1) && (! $this->is_in_same_npanxx($this->original['start'], $this->original['end']))) {
            throw new \Exception('Range Start and End must be in same NPA NXX for NANP Numbers');
        }

        // Check to make sure that block is not greater than or equal to 10000 DIDs. 0000 - 9999 - This will help keep all in same NPANXX
        $diff = $this->original['end'] - $this->original['start'];
        if ($diff >= 10000) {
            throw new \Exception('Error: Block must not be greater than 10000 DIDs');
        }

        // Check if country code is 1 and number cannot be more than 10 digits.
        if ($this->original['country_code'] == 1) {
            if ((! $this->less_10digits($this->original['start']) || (! $this->less_10digits($this->original['end'])))) {
                throw new \Exception('NANP Start or End Range must not be more than 10 digits long');
            }
        }
        */
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
