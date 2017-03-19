<?php

namespace App\Http\Controllers;

use DB;
use App\Cucmsiteconfigs;
use App\Cucmphoneconfigs;
use Illuminate\Http\Request;
// Include the JWT Facades shortcut
use Tymon\JWTAuth\Facades\JWTAuth;

//use Dingo\Api\Routing\Helpers;

class CucmReportsController extends Controller
{
    //use Helpers;
    public function __construct()
    {
        // Only authenticated users can make these calls
        $this->middleware('jwt.auth');
    }

	public function sitesSummary()
    {
        $user = JWTAuth::parseToken()->authenticate();
        
		if (! $user->can('read', Cucmsiteconfigs::class)) {
            abort(401, 'You are not authorized');
        }
		
		$sites = Cucmsiteconfigs::all();

        $response = [
                    'status_code'    => 200,
                    'success'        => true,
                    'message'        => '',
                    'response'          => $sites,
                    ];

        return response()->json($response);
    }
	
    public function siteE911TrunkingReport()
    {
        $user = JWTAuth::parseToken()->authenticate();

		if (! $user->can('read', Cucmsiteconfigs::class)) {
			abort(401, 'You are not authorized');
		}
		
		$sites = DB::table('cucmsite')->select('sitecode', 'trunking','e911')->orderBy('sitecode')->get();
		
        $response = [
                    'status_code'    => 200,
                    'success'        => true,
                    'message'        => '',
                    'response'          => $sites,
                    ];

        return response()->json($response);
    }

}
