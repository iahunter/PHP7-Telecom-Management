<?php

namespace App\Http\Controllers;

use App\CucmCDR;
use App\CucmCMR;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use phpseclib\Net\SFTP as Net_SFTP;
use Tymon\JWTAuth\Facades\JWTAuth;

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
                    ->where(function ($query) use ($search) {
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

    public function list_last_24hr_calls_with_loss(Request $request)
    {
        // Historical Log Query
        $user = JWTAuth::parseToken()->authenticate();

        // Check Role of user
        if (! $user->can('read', CucmCMR::class)) {
            abort(401, 'You are not authorized');
        }

        $start = Carbon::now()->subHours(24);
        $end = Carbon::now()->addHours(6);

        if (
            ! \App\CucmCMR::whereBetween('dateTimeStamp', [$start, $end])->where('packetLossPercent', '>', 1)->where('packetLossPercent', '<=', 100)->count()
        ) {
            abort(404, 'No records found');
        } else {
            $calls = \App\CucmCMR::whereBetween('dateTimeStamp', [$start, $end])

                ->where('packetLossPercent', '>', 1)
                ->where('packetLossPercent', '<=', 100)
                ->where('numberPacketsReceived', '>', 500)
                ->orderby('dateTimeStamp', 'desc')
                ->get();
        }

        $call_cmrs = [];

        foreach ($calls as $call) {

            //print_r($call['globalCallID_callId']);
            $call_cdr = \App\CucmCDR::where('globalCallID_callId', $call['globalCallID_callId'])->first();

            if ($call_cdr) {
                //print_r($call_cdr);

                $call['callingPartyNumber'] = $call_cdr['callingPartyNumber'];
                $call['originalCalledPartyNumber'] = $call_cdr['originalCalledPartyNumber'];
                $call['finalCalledPartyNumber'] = $call_cdr['finalCalledPartyNumber'];
                $call['origIpv4v6Addr'] = $call_cdr['origIpv4v6Addr'];
                $call['destIpv4v6Addr'] = $call_cdr['destIpv4v6Addr'];
                $call['origDeviceName'] = $call_cdr['origDeviceName'];
                $call['destDeviceName'] = $call_cdr['destDeviceName'];
                $call['cdr'] = $call_cdr;
            } else {
                $call['callingPartyNumber'] = '';
                $call['originalCalledPartyNumber'] = '';
                $call['finalCalledPartyNumber'] = '';
                $call['origIpv4v6Addr'] = '';
                $call['destIpv4v6Addr'] = '';
                $call['origDeviceName'] = '';
                $call['destDeviceName'] = '';
                $call['cdr'] = '';
            }

            $call_cmrs[] = $call;
        }

        $calls = $call_cmrs;

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
        if (! $user->can('read', CucmCMR::class)) {
            abort(401, 'You are not authorized');
        }

        $start = Carbon::parse($request->start);
        $end = Carbon::parse($request->end)->addDay()->addHours(6);

        if (
            ! \App\CucmCMR::whereBetween('dateTimeStamp', [$start, $end])->where('packetLossPercent', '>', 1)->where('packetLossPercent', '<=', 100)->count()
        ) {
            abort(404, 'No records found');
        } else {
            $calls = \App\CucmCMR::whereBetween('dateTimeStamp', [$start, $end])

                ->where('packetLossPercent', '>', 1)
                ->where('packetLossPercent', '<=', 100)
                ->where('numberPacketsReceived', '>', 500)
                ->orderby('dateTimeStamp')
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
