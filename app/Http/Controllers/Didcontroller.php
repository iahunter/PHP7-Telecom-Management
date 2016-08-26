<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Didblock;
use App\Did;
// Include the JWT Facades shortcut
use Tymon\JWTAuth\Facades\JWTAuth;

class Didcontroller extends Controller
{
    public function __construct()
    {
        // Only authenticated users can make these calls
        $this->middleware('jwt.auth');
    }

    public function didblock_validation($request)
    {
        // Check if Country Code is set.
        if (empty($request['country_code']) || $request['country_code'] == '') {
            throw new \Exception('No Country Code Set');
        }
        // Check if Country Code are numbers only.
        if (! preg_match('/^[0-9]+$/', $request['country_code'])) {
            throw new \Exception('Country Code must be numeric');
        }
        // Check if Name is set
        if (empty($request['name']) || $request['name'] == '') {
            throw new \Exception('No Name Set');
        }
        // Check if start is set
        if (empty($request['start']) || $request['start'] == '') {
            throw new \Exception('No Range Start Set');
        }
        // Check if end is set
        if (empty($request['end']) || $request['end'] == '') {

            // If there is no range end then create a single entry. - This is good for POTS lines/single number ranges.
            $request['end'] = $request['start'];
            //throw new \Exception('No Range End Set');
        }
        /* Check if start and end are numeric numbers. This wasn't working with + digits.
        if (!is_numeric($request['start']) || (!is_numeric($request['end']))){
            throw new \Exception('Start and End must be numeric numbers');
        }*/

        // Check if start are numbers.
        if (! preg_match('/^[0-9]+$/', $request['start'])) {
            throw new \Exception('Range start must be numeric');
        }
        // Check if end are numbers.
        if (! preg_match('/^[0-9]+$/', $request['end'])) {
            throw new \Exception('Range start must be numeric');
        }

        // Check to make sure start is not greater than end.
        if ($request['start'] > $request['end']) {
            throw new \Exception('Error: Range start must not be greater than range end');
        }
		
		// Check if start and end are in same NPA NXX if they have country Code of 1. 
        if (($request['country_code'] == 1) && (!$this->is_in_same_npanxx($request['start'], $request['end']))) {
            throw new \Exception('Range Start and End must be in same NPA NXX for NANP Numbers');
        }

        // Check to make sure that block is not greater than or equal to 10000 DIDs. 0000 - 9999 - This will help keep all in same NPANXX
        $diff = $request['end'] - $request['start'];
        if ($diff >= 10000) {
            throw new \Exception('Error: Block must not be greater than 10000 DIDs');
        }
		
		// Check if country code is 1 and number cannot be more than 10 digits. 
        if ($request['country_code'] == 1) {
			if ((!$this->less_10digits($request['start']) || (!$this->less_10digits($request['end'])))) {
				throw new \Exception('NANP Start or End Range must not be more than 10 digits long');
			}
		}
        return $request;
    }
	
	
	public function less_10digits($num)
	{
		$num_length = strlen((string)$num);
		if($num_length <= 10) {
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
		
		if ($npanxx_start == $npanxx_end){
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
        if (Didblock::where([['country_code', '=', $country_code], ['start', '<=', $start], ['end', '>=', $start]])->count()) {
            return true;
        }
        // Check if block end is between any existing blocks.
        if (Didblock::where([['country_code', '=', $country_code], ['start', '<=', $end], ['end', '>=', $end]])->count()) {
            return true;
			
			// *** FUTURE ***
			// Need to return an array with ID numbers of overlapping ranges to put in the exception. 
        }
    }

    public function overlap_db_check_old($ranges)
    {
        /*
        * Deprecated Function - This function is way to cpu and memory intensive. Being abandonded.
        */

        $didblocks = Didblock::all();
        foreach ($ranges as $val) {
            foreach ($didblocks as $didblock) {
                $min = $didblock->start;
                $max = $didblock->end;
                //if($this->is_in_range($val,$min,$max)){
                    //return true;

                if ($val >= $min && $val <= $max) {                // Check to see if the start or end is inside existing ranges.
                    return true;
                }
            }
        }
    }

    /*
     * @SWG\Get(
     *     path="/telephony/api/didblock",
     *     @SWG\Response(response="200", description="Get Did Blocks with JSON web token by TLS client certificate authentication")
     * )
     */

    public function listDidblock()
    {
        $user = JWTAuth::parseToken()->authenticate();
        $didblocks = Didblock::all();
        $show = [];
        foreach ($didblocks as $didblock) {
            if ($user->can('read', $didblock)) {
                // hide the following fields from the didblock list view
                unset($didblock->deleted_at);

                $show[] = $didblock;
            }
        }
        $response = [
                    'success'   => true,
                    'message'   => '',
                    'didblocks' => $show,
                    ];

        return response()->json($response);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    // Read DID Block
    public function getDidblock(Request $request, $didblock_id)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $didblock = Didblock::find($didblock_id);
        if (! $user->can('read', $didblock)) {
            abort(401, 'You are not authorized to view didblock '.$didblock_id);
        }
        /****************************************************
            * Do all error checking here
            * Valid name, overlapping,
            * Create Role
        *****************************************************/
        // Create

        $response = [
                    'success'  => true,
                    'message'  => '',
                    'request'  => $request->all(),
                    'didblock' => $didblock,
                    ];

        return response()->json($response);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    // Create DID Block
    /*
    public function createDid($request)
    {
        // Did Block Validation
        $did = Did::create([$request]);

        $response = [
                    'success' => true,
                    'message' => '',
                    'request' => $request,
                    'didblock' => $did,
                    ];

        return response()->json($response);
    }
    */

    public function createDidblock(Request $request)
    {
        // Get and parse the user token and authenticate the user by token.
        $user = JWTAuth::parseToken()->authenticate();

        // Check Role of user - Must have create privledges
        if (! $user->can('create', Didblock::class)) {
            abort(401, 'You are not authorized to create new did blocks');
        }
        /****************************************************
            * Do all error checking here
            * Valid name, overlapping,
            * Create Role
        *****************************************************/

        // Did Block Validation
        $request = $this->didblock_validation($request);

        $ranges = [];                                                    // Build array to pass into overlap checker
        $ranges['country_code'] = $request['country_code'];                // Append the Start Range Number
        $ranges['start'] = $request['start'];                            // Append the Start Range Number
        $ranges['end'] = $request['end'];                                // Append the End Range Number

        // Check if overlap comes back false then Add the Block.
        $count = $this->overlap_db_check($ranges);

        if (! $count) {
            $didblock = Didblock::create($request->all());
            //$didblock_id = $didblock->id;
            //$didblock_id = $didblock->didblock->id;
            //$didblock_id = $didblock['didblock']['id'];
            $response = [
                        'success'  => true,
                        'message'  => '',
                        'request'  => $request->all(),
                        'didblock' => $didblock,
                        ];

            return response()->json($response);
        } else {
            throw new \Exception('Block overlapping with existing ranges');
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    // Create DID Block


    //************THIS NEEDS WORK ************************
    // Might want to use the following for adds and updates:
    //Model::updateOrCreate(array('search_key' => 'search_value'), array('key' => 'value'));

    public function updateDidblock(Request $request, $didblock_id)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $didblock = Didblock::find($didblock_id);
        if (! $user->can('update', $didblock)) {
            abort(401, 'You are not authorized to view didblock '.$didblock_id);
        }
        /****************************************************
            * Do all error checking here
            * Valid name, overlapping,
            * Create Role
        *****************************************************/
        // Create
        //$didblock = Didblock::create($request->all());
        $didblock->fill($request->all());
        $didblock->save();

        $response = [
                    'success'  => true,
                    'message'  => '',
                    'request'  => $request->all(),
                    'didblock' => $didblock,
                    ];

        return response()->json($response);
    }

    // Delete DID Block
    public function deleteDidblock(Request $request, $didblock_id)
    {
        $user = JWTAuth::parseToken()->authenticate();
        if (! $user->can('delete', Didblock::class)) {
            abort(401, 'You are not authorized to delete did block id '.$account_id);
        }

        $didblock = Didblock::find($didblock_id);                                        // Find the block in the database by id
        $didblock->delete();                                                            // Delete the did block.
        $response = [
                    'success'    => true,
                    'message'    => 'Did Block '.$didblock_id.' successfully deleted',
                    'deleted_at' => $didblock->deleted_at, ];

        return response()->json($response);
    }
}
