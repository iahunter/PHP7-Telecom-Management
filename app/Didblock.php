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

    public function less_10digits($num)
    {
        $num_length = strlen((string) $num);
        if ($num_length <= 10) {
            return true;
        }
    }

    public function is_in_same_npanxx($start, $end)
    {
        // Function to check if the start and end begin with the same 6 digits.
        $startarray = str_split($start, 6);
        $endarray = str_split($end, 6);
        $npanxx_start = $startarray[0];
        $npanxx_end = $endarray[0];

        if ($npanxx_start == $npanxx_end) {
            //print "Equal \n";
            return true;
        }
    }

    public function is_in_range($val, $min, $max)
    {
        // Simple checker if val is between min and max variables. Returns true or 1 if it does.
        return $val >= $min && $val <= $max;
    }

    public function overlap_db_check($ranges)
    {
        /*
        * This function checks if the block that is being added overlaps with an existing block that exists in the DB.
        * Feed in the country_code, start, and end as an associative array called $ranges.
        */
        $country_code = $ranges['country_code'];
        $start = $ranges['start'];
        $end = $ranges['end'];

        /* Alternative Method - DB Method using count().
        if(DB::table('did_block')->where([['country_code','=', $country_code],['start','>=',$start],['end','<=',$end]])->count()){
            return true;
        }
        */

        // Model Method using count(). If the number of rows that overlaps with the range return true.
        // Check if block start is between any existing blocks.
        if (self::where([['country_code', '=', $country_code], ['start', '<=', $start], ['end', '>=', $start]])->count()) {
            return true;
        }
        // Check if block end is between any existing blocks.
        if (self::where([['country_code', '=', $country_code], ['start', '<=', $end], ['end', '>=', $end]])->count()) {
            return true;

            // *** FUTURE ***
            // Need to return an array with ID numbers of overlapping ranges to put in the exception.
        }
    }

    protected function validate()
    {
        if (! $this->name) {
            throw new \Exception('No Name Set');
        }
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


        // Check if Name is set
        if (! $this->country_code) {
            if (empty($this->original['name']) || $this->original['name'] == '') {
                throw new \Exception('No Name Set');
            }
        }

        if (! $this->start) {
            throw new \Exception('No Range Start Set');
        }

        if (! $this->end) {
            throw new \Exception('No Range End Set');
        }

        // Check if start are numbers.
        if (! preg_match('/^[0-9]+$/', $this->start)) {
            throw new \Exception('Range start must be numeric');
        }
        // Check if end are numbers.
        if (! preg_match('/^[0-9]+$/', $this->end)) {
            throw new \Exception('Range start must be numeric');
        }

        // Check to make sure start is not greater than end.
        if ($this->start > $this->end) {
            throw new \Exception('Error: Range start must not be greater than range end');
        }

        // Prevent 11+ digit DIDs
        if ($this->start > 9999999999 || $this->end > 9999999999) {
            throw new \Exception('Error: DID too long');
        }

        // Check if start and end are in same NPA NXX if they have country Code of 1.
        if (($this->country_code == 1) && (! $this->is_in_same_npanxx($this->start, $this->end))) {
            throw new \Exception('Range Start and End must be in same NPA NXX for NANP Numbers');
        }

        // Check to make sure that block is not greater than or equal to 10000 DIDs. 0000 - 9999 - This will help keep all in same NPANXX
        $diff = $this->end - $this->start;
        //dd($diff);
        if ($diff >= 10000) {
            throw new \Exception('Error: Block must not be greater than 10000 DIDs');
        }

        // Check if country code is 1 and number cannot be more than 10 digits.
        if ($this->country_code == 1) {
            if ((! $this->less_10digits($this->start) || (! $this->less_10digits($this->end)))) {
                throw new \Exception('NANP Start or End Range must not be more than 10 digits long');
            }
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
