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
}
