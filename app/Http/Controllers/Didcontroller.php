<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Didblock;
use App\Did;
// Include the JWT Facades shortcut
use Tymon\JWTAuth\Facades\JWTAuth;

//use Dingo\Api\Routing\Helpers;

class Didcontroller extends Controller
{
	//use Helpers;
    public function __construct()
    {
        // Only authenticated users can make these calls
        $this->middleware('jwt.auth');
    }
	
	public function listDidblock()
    {
        $user = JWTAuth::parseToken()->authenticate();
        $didblocks = Didblock::all();
        $show = [];
        foreach ($didblocks as $didblock) {
            if ($user->can('read', $didblock)) {
                unset($didblock->deleted_at);

                $show[] = $didblock;
            }
        }
        $response = [
                    'status_code'    => 200,
                    'success'        => true,
                    'message'        => '',
                    'didblocks'      => $show,
                    ];

        return response()->json($response);
    }

    public function getDidblock(Request $request, $didblock_id)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $didblock = Didblock::find($didblock_id);
        if (! $user->can('read', $didblock)) {
            abort(401, 'You are not authorized to view didblock '.$didblock_id);
        }

        $response = [
                    'status_code'    => 200,
                    'success'        => true,
                    'message'        => '',
                    'request'        => $request->all(),
                    'didblock'       => $didblock,
                    ];

        return response()->json($response);
    }

    public function createDidblock(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        // Check Role of user
        if (! $user->can('create', Didblock::class)) {
            abort(401, 'You are not authorized to create new did blocks');
        }

        $didblock = Didblock::create($request->all());

        $response = [
                    'status_code'    => 200,
                    'success'        => true,
                    'message'        => '',
                    'request'        => $request->all(),
                    'didblock'       => $didblock,
                    ];

        return response()->json($response);
    }

    public function updateDidblock(Request $request, $didblock_id)
    {
        $user = JWTAuth::parseToken()->authenticate();

        // Find record by id
        $didblock = Didblock::find($didblock_id);

        // Check Role of user
        if (! $user->can('update', $didblock)) {
            abort(401, 'You are not authorized to view didblock '.$didblock_id);
        }

        $didblock->fill($request->all());
        $didblock->save();

        $response = [
                    'status_code'    => 200,
                    'success'        => true,
                    'message'        => '',
                    'request'        => $request->all(),
                    'didblock'       => $didblock,
                    ];

        return response()->json($response);
    }

    public function deleteDidblock(Request $request, $didblock_id)
    {
        $user = JWTAuth::parseToken()->authenticate();

        // Check Role of user
        if (! $user->can('delete', Didblock::class)) {
            abort(401, 'You are not authorized to delete did block id '.$didblock_id);
        }

        $didblock = Didblock::find($didblock_id);                                        // Find the block in the database by id
        $didblock->delete();                                                            // Delete the did block.
        $response = [
                    'status_code'    => 200,
                    'success'        => true,
                    'message'        => 'Did Block '.$didblock_id.' successfully deleted',
                    'deleted_at'     => $didblock->deleted_at, ];

        return response()->json($response);
    }

/*
##################################################################################################################################################

    Begin Work on Did API

##################################################################################################################################################
/**/
	/*
    public function listDidbyBlockID(Request $request, $didblock_id)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $dids = \App\Did::where('didblock_id', $didblock_id)->get();
        //return response()->json($dids);
        $show = [];
        foreach ($dids as $did) {
            if ($user->can('read', $did)) {
                unset($did->deleted_at);

                $show[] = $did;
            }
        }
        $response = [
                    'status_code'    => 200,
                    'success'        => true,
                    'message'        => '',
                    'dids'           => $show,
                    ];

        return response()->json($response);
    }*/
	
	public function listDidbyBlockID(Request $request, $didblock_id)
    {
        $user = JWTAuth::parseToken()->authenticate();
        if ($user->can('read', Did::class)) {
			$dids = \App\Did::where('didblock_id', $didblock_id)->get();
        }
		//dd($dids);
        $response = [
                    'status_code'    => 200,
                    'success'        => true,
                    'message'        => '',
                    'dids'           => $dids,
                    ];

        return response()->json($response);
    }

    public function getDid(Request $request, $did_id)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $did = Did::find($did_id);
        if (! $user->can('read', $did)) {
            abort(401, 'You are not authorized to view didblock '.$did_id);
        }

        $response = [
                    'status_code'    => 200,
                    'success'        => true,
                    'message'        => '',
                    'request'        => $request->all(),
                    'did'            => $did,
                    ];

        return response()->json($response);
    }
	
	
	public function searchDidNumber(Request $request, $number_search)
    {
        $user = JWTAuth::parseToken()->authenticate();
		
		if (! $user->can('read', Did::class)) {
            abort(401, 'You are not authorized to view didblock '.$did);
        }
		
		// Search for DID by numberCheck if there are any matches. 
		if (! Did::where([['number','like', $number_search.'%']])->count()){
			abort(401, 'No number found matching search: '.$number_search); 
		}

		// Search for numbers like search. 
		$dids = Did::where([['number','like', $number_search.'%']])->get();
		
		//return "HERE ".$did;

        $response = [
                    'status_code'    => 200,
                    'success'        => true,
                    'message'        => '',
                    'request'        => $request->all(),
                    'dids'            => $dids,
                    ];

        return response()->json($response);
    }

    public function updateDid(Request $request, $did_id)
    {
        $user = JWTAuth::parseToken()->authenticate();

        // Find record by id
        $did = Did::find($did_id);

        // Check Role of user
        if (! $user->can('update', $did)) {
            abort(401, 'You are not authorized to view didblock '.$did_id);
        }

        $did->fill($request->all());
        $did->save();

        $response = [
                    'status_code'    => 200,
                    'success'        => true,
                    'message'        => '',
                    'request'        => $request->all(),
                    'did'            => $did,
                    ];

        return response()->json($response);
    }
	
	

    /* Not sure we want to advertise delete individual DIDs. Leaving this commented out for now.

    public function deleteDid(Request $request, $did_id)
    {
        $user = JWTAuth::parseToken()->authenticate();

        // Check Role of user
        if (! $user->can('delete', Did::class)) {
            abort(401, 'You are not authorized to delete did id '.$account_id);
        }

        $did = Did::find($did_id);                                        // Find the block in the database by id
        $did->delete();                                                            // Delete the did block.
        $response = [
                    'status_code'    => 200,
                    'success'        => true,
                    'message'        => 'Did Block '.$did_id.' successfully deleted',
                    'deleted_at'     => $did->deleted_at, ];

        return response()->json($response);

    }
    */
}
