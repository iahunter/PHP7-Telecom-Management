<?php

namespace App\Http\Controllers;

use DB;
use App\Calls;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class Callcontroller extends Controller
{
    //use Helpers;
    public function __construct()
    {
        // Only authenticated users can make these calls
        $this->middleware('jwt.auth');
    }

    public function listcallstats()
    {
        $user = JWTAuth::parseToken()->authenticate();
        if (! $user->can('read', Calls::class)) {
            abort(401, 'You are not authorized');
        }

        $calls = Calls::all();
        $stats = [];
        foreach ($calls as $call) {
            $call['stats'] = json_decode($call['stats']);
            $stats[] = $call;
        }
        $response = [
                    'status_code'    => 200,
                    'success'        => true,
                    'message'        => '',
                    'result'         => $stats,
                    ];

        return response()->json($response);
    }

    public function list_last_7days_callstats()
    {
        $user = JWTAuth::parseToken()->authenticate();
        if (! $user->can('read', Calls::class)) {
            abort(401, 'You are not authorized');
        }

        $currentDate = \Carbon\Carbon::now();
        $now = $currentDate->toDateTimeString();
        $weekago = $currentDate->subHours(168)->toDateTimeString();

        $calls = Calls::whereBetween('created_at', [$weekago, $now])->get();

        $stats = [];
        foreach ($calls as $call) {
            $call['stats'] = json_decode($call['stats']);
            $stats[] = $call;
        }
        $response = [
                    'status_code'    => 200,
                    'success'        => true,
                    'message'        => '',
                    'result'         => $stats,
                    ];

        return response()->json($response);
    }

    public function list_last_24hrs_callstats()
    {
        $user = JWTAuth::parseToken()->authenticate();
        if (! $user->can('read', Calls::class)) {
            abort(401, 'You are not authorized');
        }

        $currentDate = \Carbon\Carbon::now();
        $now = $currentDate->toDateTimeString();
        $weekago = $currentDate->subHours(24)->toDateTimeString();

        $calls = Calls::whereBetween('created_at', [$weekago, $now])->get();

        $stats = [];
        foreach ($calls as $call) {
            $call['stats'] = json_decode($call['stats']);
            $stats[] = $call;
        }
        $response = [
                    'status_code'    => 200,
                    'success'        => true,
                    'message'        => '',
                    'result'         => $stats,
                    ];

        return response()->json($response);
    }

	/*
    public function list_last_month_callstats()
    {
        $user = JWTAuth::parseToken()->authenticate();
        if (! $user->can('read', Calls::class)) {
            abort(401, 'You are not authorized');
        }

        $currentDate = \Carbon\Carbon::now();
        $end = $currentDate->toDateTimeString();
        $start = $currentDate->subMonth()->toDateTimeString();

        $calls = Calls::whereBetween('created_at', [$start, $end])->get();

        $stats = [];
        foreach ($calls as $call) {
            $call['stats'] = json_decode($call['stats']);
            $stats[] = $call;
        }
        $response = [
                    'status_code'    => 200,
                    'success'        => true,
                    'message'        => '',
                    'result'         => $stats,
                    ];

        return response()->json($response);
    }
	
	*/
	public function list_last_month_callstats()
    {
		// This is a new version of returning the peak of each date so make our graph a little smoother. 
        $user = JWTAuth::parseToken()->authenticate();
        if (! $user->can('read', Calls::class)) {
            abort(401, 'You are not authorized');
        }

        $currentDate = \Carbon\Carbon::now();
        $end = $currentDate->toDateTimeString();
        $start = $currentDate->subMonth()->toDateTimeString();

        $calls = Calls::whereBetween('created_at', [$start, $end])->get();

        $stats = [];
        foreach ($calls as $call) {
			$date = $call['created_at'];
			$date = Carbon::parse($call['created_at']);
			$date = $date->toDateString();
			
			$call['stats'] = json_decode($call['stats']);
			
			if(isset($stats[$date])){
				if($stats[$date]['totalCalls'] >= $call['totalCalls']){
					continue;
				}
			}

			$stats[$date] = $call;
			
        }
        $response = [
                    'status_code'    => 200,
                    'success'        => true,
                    'message'        => '',
                    'result'         => $stats,
                    ];

        return response()->json($response);
    }

    public function list_callstats_by_date_range(Request $request)
    {
        // Historical Log Query
        $user = JWTAuth::parseToken()->authenticate();

        // Check Role of user
        if (! $user->can('read', Calls::class)) {
            abort(401, 'You are not authorized');
        }

        $start = Carbon::parse($request->start);
        $end = Carbon::parse($request->end)->addDay()->addHours(6);

        if (! \App\Calls::whereBetween('created_at', [$start, $end])->count()) {
            abort(404, 'No records found');
        } else {
            $calls = \App\Calls::whereBetween('created_at', [$start, $end])->orderby('created_at')->get();
        }

        $response = [
                    'status_code'          => 200,
                    'success'              => true,
                    'message'              => '',
                    'count'                => count($calls),
                    'request'              => $request->all(),
                    'result'               => $calls,
                    ];

        return response()->json($response);
    }
}
