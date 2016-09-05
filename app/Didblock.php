<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Did;

class Didblock extends Model
{
    use SoftDeletes;
    protected $table = 'did_block';
    protected $fillable = ['country_code', 'name', 'carrier', 'start', 'end', 'type', 'comment'];

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

        // Cascade Soft Deletes Child Dids
        static::deleting(function ($didblock) {
            foreach ($didblock->dids()->get() as $did) {
                $did->delete();
            }
        });
        // Cascade Soft Restore Child Dids
        /*
        static::restoring(function ($didblock) {
            foreach ($didblock->dids()->get() as $did){
                $did->restore();
            }
        });
        */
    }

    public function dids()
    {
        // Add children
        return $this->hasMany(Did::class);
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
            //echo PHP_EOL.'Creating DID number '.$number;
            $response = $this->dids()->create($request); // This goes out and builds the new did transaction. The parent ID is joined automatically.
            //$this->log($response);
        }
    }

    public function not_10digits($num)
    {
        // Checks if number is 10 digits in length.
        $num_length = strlen((string) $num);
        if ($num_length == 10) {
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
            return true;
        }
    }

    public function is_in_range($val, $min, $max)
    {
        // Simple checker if val is between min and max variables. Returns true or 1 if it does.
        return $val >= $min && $val <= $max;
    }

    public function overlap_db_check()
    {
        /*
            This checks to make sure that the start and end numbers of the new range are not between the existing start and end range numbers.
        /**/
        //echo PHP_EOL."1 new Start is between) ".self::where([['country_code', '=', $this->country_code], ['start', '<=', $this->start], ['end', '>=', $this->start]])->toSql();
        if (self::where([['country_code', '=', $this->country_code], ['start', '<=', $this->start], ['end', '>=', $this->start]])->count()) {
            throw new \Exception('This block overlapps with existing');
        }
        //echo PHP_EOL."2 new End is between) ".self::where([['country_code', '=', $this->country_code], ['start', '<=', $this->end], ['end', '>=', $this->end]])->toSql();
        if (self::where([['country_code', '=', $this->country_code], ['start', '<=', $this->end], ['end', '>=', $this->end]])->count()) {
            throw new \Exception('This block overlapps with existing');
        }

        /*
            This checks to make sure that the exising start and end range numbers are not between the new range start and end range numbers.
        /**/
        //echo PHP_EOL."3 existing start is between) ".self::where([['country_code', '=', $this->country_code]])->whereBetween('start', [$this->start, $this->end])->toSql();
        if (self::where([['country_code', '=', $this->country_code]])->whereBetween('start', [$this->start, $this->end])->count()) {
            throw new \Exception('This block overlapps with existing');
        }
        //echo PHP_EOL."4 existing end is between) ".self::where([['country_code', '=', $this->country_code]])->whereBetween('end', [$this->start, $this->end])->toSql();
        if (self::where([['country_code', '=', $this->country_code]])->whereBetween('end', [$this->start, $this->end])->count()) {
            throw new \Exception('This block overlapps with existing');
        }

        return true;
    }

    protected function validate()
    {
        // Check if range start is set
        if (! $this->start) {
            throw new \Exception('No Range Start Set');
        }
        // Check if range end is set
        if (! $this->end) {
            throw new \Exception('No Range End Set');
        }
        // Check for overlapping ranges.
        if (! $this->id) {
            $this->overlap_db_check();
        }
        // Check if Name is set.
        if (! $this->name) {
            throw new \Exception('No Name Set');
        }
        // Check if type is set.
        if (! $this->type) {
            throw new \Exception('No type Set');
        }
        // Check if start and end are in same NPA NXX if they have country Code of 1.
        if (($this->type != 'public') && ($this->type != 'private')) {
            throw new \Exception('Type must be set to public or private');
        }
        if (isset($this->original['country_code']) && $this->original['country_code'] !== $this->country_code) {
            throw new \Exception('Validation error, Country Code can not be altered once created');
        }
        // Make sure the start and end attributes are impossible to change once set
        if (isset($this->original['start']) && $this->original['start'] !== $this->start) {
            throw new \Exception('Validation error, start range can not be altered once created');
        }
        if (isset($this->original['end']) && $this->original['end'] !== $this->end) {
            throw new \Exception('Validation error, end range can not be altered once created');
        }
        // Check if country code is a number.
        if (! preg_match('/^[0-9]+$/', $this->country_code)) {
            throw new \Exception('Country Code must be numeric');
        }
        // Check if start are numbers.
        if (! preg_match('/^[0-9]+$/', $this->start)) {
            throw new \Exception('Range start must be numeric');
        }
        // Check if end are numbers.
        if (! preg_match('/^[0-9]+$/', $this->end)) {
            throw new \Exception('Range end must be numeric');
        }
        // Check to make sure start is not greater than end.
        if ($this->start > $this->end) {
            throw new \Exception('Error: Range start must not be greater than range end');
        }
        // Check if start and end are in same NPA NXX if they have country Code of 1.
        if (($this->country_code == 1) && (! $this->is_in_same_npanxx($this->start, $this->end))) {
            throw new \Exception('Range Start and End must be in same NPA NXX for NANP Numbers');
        }
        // Check to make sure that block is not greater than or equal to 10000 DIDs. 0000 - 9999 - This will help keep all in same NPANXX
        $diff = $this->end - $this->start;
        if ($diff >= 10000) {
            throw new \Exception('Error: Block must not be greater than 10000 DIDs');
        }
        // Check if type is public and country code is 1 and number must be 10 digits.
        if (($this->type == 'public') && ($this->country_code == 1)) {
            if ((! $this->not_10digits($this->start) || (! $this->not_10digits($this->end)))) {
                throw new \Exception('NANP Start or End Range must be 10 digits');
            }
        }
        // Check if exceeds max of 255
        if (strlen($this->name) > 255) {
            throw new \Exception('name exceeded 255 characters');
        }
        // Check if exceeds max of 255
        if (strlen($this->status) > 255) {
            throw new \Exception('status exceeded 255 characters');
        }
        // Check if exceeds max of 255
        if (strlen($this->system_id) > 255) {
            throw new \Exception('system_id exceeded 255 characters');
        }
    }
}
