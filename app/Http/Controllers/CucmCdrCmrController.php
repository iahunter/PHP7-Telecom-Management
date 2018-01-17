<?php

namespace App\Http\Controllers;

use App\CucmCDR;
use App\CucmCMR;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use phpseclib\Net\SFTP as Net_SFTP;
use Illuminate\Support\Facades\Cache;

class CucmCdrCmrController extends Controller
{
    public function __construct()
    {
        // Only authenticated users can make these calls
        $this->middleware('jwt.auth');
    }

    public function searchCDR(Request $request, $column, $search)
    {
        $user = JWTAuth::parseToken()->authenticate();

        // Check Role of user
        if (! $user->can('read', CucmCDR::class)) {
            abort(401, 'You are not authorized');
        }

        $column = $request->column;
        $search = $request->search;

        if (! \App\CucmCDR::where($column, 'like', "%{$search}%")->count()) {
            abort(404, 'No records found');
        } else {
            $result = \App\CucmCDR::where($column, 'like', "%{$search}%")->orderby('dateTimeConnect')->get();
        }

        $response = [
                    'status_code'          => 200,
                    'success'              => true,
                    'message'              => '',
                    'request'              => $request->all(),
                    'result'               => $result,
                    ];

        return response()->json($response);
    }

    public function list_last_24hr_calls_by_number_search(Request $request)
    {
        // Historical Log Query
        $user = JWTAuth::parseToken()->authenticate();

        // Check Role of user
        if (! $user->can('read', CucmCDR::class)) {
            abort(401, 'You are not authorized');
        }

        $search = $request->search;
        $start = Carbon::now()->subHours(24);
        $end = Carbon::now()->addHours(6);

        $calls = \App\CucmCDR::whereBetween('dateTimeConnect', [$start, $end])
                    ->where(function ($query) {
                        $query->where('callingPartyNumber', 'like', "%{$search}%")
                                      ->orWhere('originalCalledPartyNumber', 'like', "%{$search}%")
                                      ->orWhere('finalCalledPartyNumber', 'like', "%{$search}%");
                    })
                    ->orderby('dateTimeConnect')
                    ->get();

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

    public function list_calls_by_date_range_with_loss(Request $request)
    {
        // Historical Log Query
        $user = JWTAuth::parseToken()->authenticate();

        // Check Role of user
        if (! $user->can('read', CucmCDR::class)) {
            abort(401, 'You are not authorized');
        }

        $start = Carbon::parse($request->start);
        $end = Carbon::parse($request->end)->addDay()->addHours(6);

        if (
            ! \App\CucmCDR::whereBetween('dateTimeConnect', [$start, $end])->where('ingress_lost_ptks', '>', 100)->count() &&
            ! \App\CucmCDR::whereBetween('dateTimeConnect', [$start, $end])->where('egress_lost_ptks', '>', 100)->count()
        ) {
            abort(404, 'No records found');
        } else {
            $calls = \App\CucmCDR::whereBetween('dateTimeConnect', [$start, $end])

                ->where(function ($query) {
                    $query->where('ingress_lost_ptks', '>', 100)
                              ->orWhere('egress_lost_ptks', '>', 100);
                })
                ->orderby('dateTimeConnect')
                ->get();
        }

        $response = [
                    'status_code'          => 200,
                    'success'              => true,
                    'message'              => '',
                    'request'              => $request->all(),
                    'count'                => count($calls),
                    'result'               => $calls,
                    ];

        return response()->json($response);
    }
}
