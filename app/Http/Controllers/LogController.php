<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
// Activity Logger
use Spatie\Activitylog\Models\Activity;

use Silber\Bouncer\Bouncer;

class LogController extends Controller
{
    public function log_page_name(Request $request, $name)
    {
        $user = JWTAuth::parseToken()->authenticate();

        // Log activity in request
        activity('pagelog')->causedBy($user)->withProperties($name)->log('Page Request');

        // Return Nothing
    }
	
	public function test(Request $request)
    {
		// Testing JSON inside of the .env file
		if(isset($_ENV['test'])){

			$json = json_decode($_ENV['test'], true);

			return json_encode($json, true);

		}else{
			return 'Test Failed';
		}
       
    }
}
