<?php

namespace App\Http\Controllers;

use DB;
use Carbon\Carbon;
use App\GatewayCalls;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Cache;

class GatewayCallsController extends Controller
{
    public function listcallstats()
    {
        $user = JWTAuth::parseToken()->authenticate();
        if (! $user->can('read', GatewayCalls::class)) {
            abort(401, 'You are not authorized');
        }

        $GatewayCalls = GatewayCalls::all();
        $stats = [];
        foreach ($GatewayCalls as $call) {
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
        if (! $user->can('read', GatewayCalls::class)) {
            abort(401, 'You are not authorized');
        }

        // Name of Cache key.
        $key = 'GatewayCalls:list_last_7days_GatewayCallstats';

        // Look if the report is in the cache.
        if (Cache::has($key)) {
            $stats = Cache::get($key);

            $response = [
                    'status_code'    => 200,
                    'success'        => true,
                    'message'        => '',
                    'cached'         => true,
                    'result'         => $stats,
                    ];

            return response()->json($response);
        }

        $currentDate = \Carbon\Carbon::now();
        $now = $currentDate->toDateTimeString();
        $weekago = $currentDate->subHours(168)->toDateTimeString();

        $GatewayCalls = GatewayCalls::whereBetween('created_at', [$weekago, $now])->get();

        $stats = [];
        foreach ($GatewayCalls as $call) {
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
        if (! $user->can('read', GatewayCalls::class)) {
            abort(401, 'You are not authorized');
        }

        // Name of Cache key.
        $key = 'GatewayCalls:list_last_24hrs_GatewayCallstats';

        // Look if the report is in the cache.
        if (Cache::has($key)) {
            $stats = Cache::get($key);

            $response = [
                    'status_code'    => 200,
                    'success'        => true,
                    'message'        => '',
                    'cached'         => true,
                    'result'         => $stats,
                    ];

            return response()->json($response);
        }

        $currentDate = \Carbon\Carbon::now();
        $now = $currentDate->toDateTimeString();
        $dayago = $currentDate->subHours(24)->toDateTimeString();

        $GatewayCalls = GatewayCalls::whereBetween('created_at', [$dayago, $now])->get();

        $stats = [];
        foreach ($GatewayCalls as $call) {
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
        if (! $user->can('read', GatewayCalls::class)) {
            abort(401, 'You are not authorized');
        }

        $currentDate = \Carbon\Carbon::now();
        $end = $currentDate->toDateTimeString();
        $start = $currentDate->subMonth()->toDateTimeString();

        $GatewayCalls = GatewayCalls::whereBetween('created_at', [$start, $end])->get();

        $stats = [];
        foreach ($GatewayCalls as $call) {
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
        if (! $user->can('read', GatewayCalls::class)) {
            abort(401, 'You are not authorized');
        }

        // Name of Cache key.
        $key = 'GatewayCalls:list_last_month_daily_call_peak_stats';

        // Look if the report is in the cache.
        if (Cache::has($key)) {
            $stats = Cache::get($key);

            $response = [
                    'status_code'    => 200,
                    'success'        => true,
                    'message'        => '',
                    'cached'         => true,
                    'result'         => $stats,
                    ];

            return response()->json($response);
        }

        $currentDate = \Carbon\Carbon::now();
        $end = $currentDate->toDateTimeString();
        $start = $currentDate->subMonth(3)->toDateTimeString();

        $stats = [];
        /*
        $GatewayCalls = GatewayCalls::whereBetween('created_at', [$start, $end])->get();

        foreach ($GatewayCalls as $call) {
        */

        // Use cursor to conserve memory and iterate our database records in foreach loop.
        foreach (GatewayCalls::whereBetween('created_at', [$start, $end])->cursor() as $call) {
            $date = $call['created_at'];
            $date = Carbon::parse($call['created_at']);
            $date = $date->toDateString();

            if (isset($stats[$date])) {
                if ($stats[$date]['totalGatewayCalls'] >= $call['totalGatewayCalls']) {
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
        if (! $user->can('read', GatewayCalls::class)) {
            abort(401, 'You are not authorized');
        }

        // Name of Cache key.
        $key = 'GatewayCalls:list_3_month_daily_call_peak_stats';

        // Look if the report is in the cache.
        if (Cache::has($key)) {
            $stats = Cache::get($key);
            $response = [
                    'status_code'    => 200,
                    'success'        => true,
                    'message'        => '',
                    'cached'         => true,
                    'result'         => $stats,
                    ];

            return response()->json($response);
        }

        $currentDate = \Carbon\Carbon::now();
        $end = $currentDate->toDateTimeString();
        $start = $currentDate->subMonth(3)->toDateTimeString();

        $stats = [];

        // Use cursor to conserve memory and iterate our database records in foreach loop. slower than sql query because we need to iterate thur all records.
        foreach (GatewayCalls::whereBetween('created_at', [$start, $end])->cursor() as $call) {
            $date = $call['created_at'];
            $date = Carbon::parse($call['created_at']);
            $date = $date->toDateString();

            if (isset($stats[$date])) {
                if ($stats[$date]['totalGatewayCalls'] >= $call['totalGatewayCalls']) {
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
        if (! $user->can('read', GatewayCalls::class)) {
            abort(401, 'You are not authorized');
        }

        // Name of Cache key.
        $key = 'GatewayCalls:list_3_month_daily_call_peak_stats_sql';

        // Look if the report is in the cache.
        if (Cache::has($key)) {
            $stats = Cache::get($key);

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
        SELECT DATE(created_at) as created_at, MAX(totalGatewayCalls) as totalGatewayCalls
        FROM sbc_GatewayCalls
        WHERE created_at > NOW() - INTERVAL 3 MONTH
        GROUP BY DATE(created_at)
        */

        // this could use some work to make better.
        $stats = DB::table('gateway_calls')
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
        if (! $user->can('read', GatewayCalls::class)) {
            abort(401, 'You are not authorized');
        }

        // Name of Cache key.
        $key = 'GatewayCalls:list_one_year_daily_call_peak_stats_sql';

        // Look if the report is in the cache.
        if (Cache::has($key)) {
            $stats = Cache::get($key);

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
        SELECT DATE(created_at) as created_at, MAX(totalGatewayCalls) as totalGatewayCalls
        FROM sbc_GatewayCalls
        WHERE created_at > NOW() - INTERVAL 3 MONTH
        GROUP BY DATE(created_at)
        */

        // this could use some work to make better.
        $stats = DB::table('gateway_calls')
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
        if (! $user->can('read', GatewayCalls::class)) {
            abort(401, 'You are not authorized');
        }

        $start = Carbon::parse($request->start);
        $end = Carbon::parse($request->end)->addDay()->addHours(6);

        if (! \App\GatewayCalls::whereBetween('created_at', [$start, $end])->count()) {
            abort(404, 'No records found');
        } else {
            $GatewayCalls = \App\GatewayCalls::whereBetween('created_at', [$start, $end])->orderby('created_at')->get();
        }

        $response = [
                    'status_code'          => 200,
                    'success'              => true,
                    'message'              => '',
                    'count'                => count($GatewayCalls),
                    'request'              => $request->all(),
                    'result'               => $GatewayCalls,
                    ];

        return response()->json($response);
    }
}
