<?php

namespace App\Http\Controllers;

use DB;
use App\Calls;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Cache;

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
		
		// Name of Cache key.
        $key = 'calls:list_last_7days_callstats';
		
		// Look if the report is in the cache. 
        if (Cache::has($key)) {
			
            $stats  = Cache::get($key);
			
            $response = [
                    'status_code'    => 200,
                    'success'        => true,
                    'message'        => '',
					'cached'		 => true,
                    'result'         => $stats,
                    ];

			return response()->json($response);
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
		
		// Name of Cache key.
        $key = 'calls:list_last_24hrs_callstats';
		
		// Look if the report is in the cache. 
        if (Cache::has($key)) {
			
            $stats  = Cache::get($key);
			
            $response = [
                    'status_code'    => 200,
                    'success'        => true,
                    'message'        => '',
					'cached'		 => true,
                    'result'         => $stats,
                    ];

			return response()->json($response);
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

    public function list_last_month_daily_call_peak_stats()
    {
        // This is a new version of returning the peak of each date so make our graph a little smoother.
        $user = JWTAuth::parseToken()->authenticate();
        if (! $user->can('read', Calls::class)) {
            abort(401, 'You are not authorized');
        }
		
		// Name of Cache key.
        $key = 'calls:list_last_month_daily_call_peak_stats';
		
		// Look if the report is in the cache. 
        if (Cache::has($key)) {
			
            $stats  = Cache::get($key);
			
            $response = [
                    'status_code'    => 200,
                    'success'        => true,
                    'message'        => '',
					'cached'		 => true,
                    'result'         => $stats,
                    ];

			return response()->json($response);
        }

        $currentDate = \Carbon\Carbon::now();
        $end = $currentDate->toDateTimeString();
        $start = $currentDate->subMonth(3)->toDateTimeString();

        $stats = [];
        /*
        $calls = Calls::whereBetween('created_at', [$start, $end])->get();

        foreach ($calls as $call) {
        */

        // Use cursor to conserve memory and iterate our database records in foreach loop.
        foreach (Calls::whereBetween('created_at', [$start, $end])->cursor() as $call) {
            $date = $call['created_at'];
            $date = Carbon::parse($call['created_at']);
            $date = $date->toDateString();

            $call['stats'] = json_decode($call['stats']);

            if (isset($stats[$date])) {
                if ($stats[$date]['totalCalls'] >= $call['totalCalls']) {
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

    public function list_3_month_daily_call_peak_stats()
    {
        // This is a new version of returning the peak of each date so make our graph a little smoother.
        $user = JWTAuth::parseToken()->authenticate();
        if (! $user->can('read', Calls::class)) {
            abort(401, 'You are not authorized');
        }
		
		// Name of Cache key.
        $key = 'calls:list_3_month_daily_call_peak_stats';
		
		// Look if the report is in the cache. 
        if (Cache::has($key)) {
			
            $stats  = Cache::get($key);
            $response = [
                    'status_code'    => 200,
                    'success'        => true,
                    'message'        => '',
					'cached'		 => true,
                    'result'         => $stats,
                    ];

			return response()->json($response);
        }

        $currentDate = \Carbon\Carbon::now();
        $end = $currentDate->toDateTimeString();
        $start = $currentDate->subMonth(3)->toDateTimeString();

        $stats = [];

        // Use cursor to conserve memory and iterate our database records in foreach loop. slower than sql query because we need to iterate thur all records.
        foreach (Calls::whereBetween('created_at', [$start, $end])->cursor() as $call) {
            $date = $call['created_at'];
            $date = Carbon::parse($call['created_at']);
            $date = $date->toDateString();

            $call['stats'] = json_decode($call['stats']);

            if (isset($stats[$date])) {
                if ($stats[$date]['totalCalls'] >= $call['totalCalls']) {
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

    public function list_3_month_daily_call_peak_stats_sql()
    {
        // This is a new version of returning the peak of each date so make our graph a little smoother.
        // Doing this entire function in one SQL query instead of using curser like above.
        $user = JWTAuth::parseToken()->authenticate();
        if (! $user->can('read', Calls::class)) {
            abort(401, 'You are not authorized');
        }
		
		// Name of Cache key.
        $key = 'calls:list_3_month_daily_call_peak_stats_sql';
		
		// Look if the report is in the cache. 
        if (Cache::has($key)) {
			
            $stats  = Cache::get($key);
			
            $response = [
                    'status_code'    => 200,
                    'success'        => true,
                    'message'        => '',
                    'result'         => $stats,
                    ];

			return response()->json($response);
        }

        $currentDate = \Carbon\Carbon::now();
        $end = $currentDate->toDateTimeString();
        $start = $currentDate->subMonth(3)->toDateTimeString();

        /* Trying to get this query to work.
        SELECT DATE(created_at) as created_at, MAX(totalCalls) as totalCalls
        FROM sbc_calls
        WHERE created_at > NOW() - INTERVAL 3 MONTH
        GROUP BY DATE(created_at)
        */

        // this could use some work to make better.
        $stats = DB::table('sbc_calls')
                ->select(DB::raw('DATE(created_at) as created_at'), DB::raw('MAX(totalCalls) as totalCalls'))
                ->groupBy(DB::raw('DATE(created_at)'))
                ->whereBetween('created_at', [$start, $end])->orderby('created_at')
                ->get();

        $response = [
                    'status_code'    => 200,
                    'success'        => true,
                    'message'        => '',
                    'result'         => $stats,
                    ];

        return response()->json($response);
    }

    public function list_one_year_daily_call_peak_stats_sql()
    {
        // This is a new version of returning the peak of each date so make our graph a little smoother.
        // Doing this entire function in one SQL query instead of using curser like above.
        $user = JWTAuth::parseToken()->authenticate();
        if (! $user->can('read', Calls::class)) {
            abort(401, 'You are not authorized');
        }
		
		// Name of Cache key.
        $key = 'calls:list_one_year_daily_call_peak_stats_sql';
		
		// Look if the report is in the cache. 
        if (Cache::has($key)) {
			
            $stats  = Cache::get($key);
			
            $response = [
                    'status_code'    => 200,
                    'success'        => true,
                    'message'        => '',
                    'result'         => $stats,
                    ];

			return response()->json($response);
        }

        $currentDate = \Carbon\Carbon::now();
        $end = $currentDate->toDateTimeString();
        $start = $currentDate->subYear()->toDateTimeString();

        /* Trying to get this query to work.
        SELECT DATE(created_at) as created_at, MAX(totalCalls) as totalCalls
        FROM sbc_calls
        WHERE created_at > NOW() - INTERVAL 3 MONTH
        GROUP BY DATE(created_at)
        */

        // this could use some work to make better.
        $stats = DB::table('sbc_calls')
                ->select(DB::raw('DATE(created_at) as created_at'), DB::raw('MAX(totalCalls) as totalCalls'))
                ->groupBy(DB::raw('DATE(created_at)'))
                ->whereBetween('created_at', [$start, $end])->orderby('created_at')
                ->get();

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
