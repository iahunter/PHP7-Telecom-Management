<?php

namespace App\Http\Controllers;

use DB;
use App\Sonus5k;
use Carbon\Carbon;
use App\Sonus5kCDR;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use phpseclib\Net\SFTP as Net_SFTP;
use Illuminate\Support\Facades\Cache;

class Sonus5kCDRcontroller extends Controller
{
    //use Helpers;

    public $SBCS;

    public function __construct()
    {
        // Only authenticated users can make these calls
        $this->middleware('jwt.auth');

        // Populate SBC list
        $this->SBCS = [
                        env('SONUS1'),
                        env('SONUS2'),
                        ];
    }

    public function list_calls_by_date_range(Request $request)
    {
        // Historical Log Query
        $user = JWTAuth::parseToken()->authenticate();

        // Check Role of user
        if (! $user->can('read', Sonus5kCDR::class)) {
            abort(401, 'You are not authorized');
        }

        $start = Carbon::parse($request->start);
        $end = Carbon::parse($request->end)->addDay()->addHours(6);

        if (! \App\Sonus5kCDR::whereBetween('start_time', [$start, $end])->count()) {
            abort(404, 'No records found');
        } else {
            $calls = \App\Sonus5kCDR::whereBetween('start_time', [$start, $end])->orderby('start_time')->get();
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

    public function list_calls_by_date_range_with_loss(Request $request)
    {
        // Historical Log Query
        $user = JWTAuth::parseToken()->authenticate();

        // Check Role of user
        if (! $user->can('read', Sonus5kCDR::class)) {
            abort(401, 'You are not authorized');
        }

        $start = Carbon::parse($request->start);
        $end = Carbon::parse($request->end)->addDay()->addHours(6);

        if (
            ! \App\Sonus5kCDR::whereBetween('start_time', [$start, $end])->where('ingress_lost_ptks', '>', 100)->count() &&
            ! \App\Sonus5kCDR::whereBetween('start_time', [$start, $end])->where('egress_lost_ptks', '>', 100)->count()
        ) {
            abort(404, 'No records found');
        } else {
            $calls = \App\Sonus5kCDR::whereBetween('start_time', [$start, $end])

                ->where(function ($query) {
                    $query->where('ingress_lost_ptks', '>', 100)
                              ->orWhere('egress_lost_ptks', '>', 100);
                })
                ->orderby('start_time')
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

    public function list_todays_calls_with_loss(Request $request)
    {
        // Historical Log Query
        $user = JWTAuth::parseToken()->authenticate();

        // Check Role of user
        if (! $user->can('read', Sonus5kCDR::class)) {
            abort(401, 'You are not authorized');
        }

        // Add 6 hrs to compensate for the timestamps in
        $start = Carbon::now()->subHours(72)->toDateTimeString();
        //$end = Carbon::tomorrow()->addHours(6)->toDateTimeString();
        $end = Carbon::now()->toDateTimeString();
        //return $start;
        if (! \App\Sonus5kCDR::whereBetween('start_time', [$start, $end])
            ->where(function ($query) {
                $query->where('ingress_lost_ptks', '>', 100)
                ->orWhere('egress_lost_ptks', '>', 100)
                ->count();
            })
            ) {
            abort(404, 'No records found');
        } else {
            $calls = \App\Sonus5kCDR::whereBetween('start_time', [$start, $end])

                ->where(function ($query) {
                    $query->where('ingress_lost_ptks', '>', 100)
                    ->orWhere('egress_lost_ptks', '>', 100);
                })
                ->orderby('start_time')
                ->get();
        }

        //"Ingress Number of Packets Recorded as Lost" / "Ingress Number of Audio Packets Received"
        //"Egress Number of Packets Recorded as Lost" / "Egress Number of Audio Packets Received"
        $return = [];

        foreach ($calls as $call) {
            if ($call['call_duration']) {
                $call['call_duration'] = gmdate('H:i:s', ($call['call_duration'] * 10) / 1000);
            }

            $call['disconnect_initiator_desc'] = Sonus5kCDR::get_disconnect_initiator_code($call['disconnect_initiator']);
            $call['disconnect_reason_desc'] = Sonus5kCDR::get_call_termination_code($call['disconnect_reason']);

            $ingress_pkt_loss = $call['cdr_json']['Ingress Number of Packets Recorded as Lost'];
            $ingress_pkts_recieved = $call['cdr_json']['Ingress Number of Audio Packets Received'];
            $ingress_pkt_loss_percent = $ingress_pkt_loss / ($ingress_pkts_recieved + $ingress_pkt_loss) * 100;
            $ingress_pkt_loss_percent = round($ingress_pkt_loss_percent, 2, PHP_ROUND_HALF_UP);
            $call['ingress_pkt_loss_percent'] = $ingress_pkt_loss_percent;

            $egress_pkt_loss = $call['cdr_json']['Egress Number of Packets Recorded as Lost'];
            $egress_pkts_recieved = $call['cdr_json']['Egress Number of Audio Packets Received'];
            $egress_pkt_loss_percent = $egress_pkt_loss / ($egress_pkts_recieved + $egress_pkt_loss) * 100;
            $egress_pkt_loss_percent = round($egress_pkt_loss_percent, 2, PHP_ROUND_HALF_UP);
            $call['egress_pkt_loss_percent'] = $egress_pkt_loss_percent;

            //return $call;
            if ($ingress_pkt_loss_percent > 1 || $egress_pkt_loss_percent > 1) {
                $return[] = $call;
            }
        }

        $calls = array_reverse($return);

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

    public function list_todays_attempts(Request $request)
    {
        // Historical Log Query
        $user = JWTAuth::parseToken()->authenticate();

        // Check Role of user
        if (! $user->can('read', Sonus5kCDR::class)) {
            abort(401, 'You are not authorized');
        }

        // Add 6 hrs to compensate for the timestamps in
        $start = Carbon::now()->subHours(24)->toDateTimeString();
        //$end = Carbon::tomorrow()->addHours(6)->toDateTimeString();
        $end = Carbon::now()->toDateTimeString();
        //return $start;
        if (! \App\Sonus5kCDR::whereBetween('start_time', [$start, $end])
            ->where(function ($query) {
                $query->where('type', 'ATTEMPT')
                ->count();
            })
            ) {
            abort(404, 'No records found');
        } else {
            $calls = \App\Sonus5kCDR::whereBetween('start_time', [$start, $end])

                ->where(function ($query) {
                    $query->where('type', 'ATTEMPT');
                })
                ->orderby('start_time')
                ->get();
        }

        $return = [];

        foreach ($calls as $call) {
            $call['disconnect_initiator_desc'] = Sonus5kCDR::get_disconnect_initiator_code($call['disconnect_initiator']);
            $call['disconnect_reason_desc'] = Sonus5kCDR::get_call_termination_code($call['disconnect_reason']);
            $return[] = $call;
        }

        $calls = array_reverse($return);

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

    public function list_todays_attempts_summary_report(Request $request)
    {
        // Historical Log Query
        $user = JWTAuth::parseToken()->authenticate();

        // Check Role of user
        if (! $user->can('read', Sonus5kCDR::class)) {
            abort(401, 'You are not authorized');
        }
        $return = [];

        $hours = 24;

        $now = Carbon::now()->setTimezone('UTC');
        $start = $now->subHours($hours);
        $end = Carbon::now()->setTimezone('UTC');

        // Get all the active attempt disconnet reasons in use in last 24s.
        $codes = \App\Sonus5kCDR::groupBy('disconnect_reason')
                ->select('disconnect_reason', DB::raw('count(*) as total'))
                ->whereBetween('start_time', [$start, $end])
                ->where('type', 'ATTEMPT')
                ->get();

        $x = 0;

        // get all the records for every hour in for the specified number of hours.
        while ($x != $hours) {
            $now = Carbon::now()->setTimezone('UTC');
            $starthour = $now->subHours($hours);
            $copystart = clone $starthour;
            $subhour = $copystart->addHours(1)->toDateTimeString();
            $starthour = $starthour->toDateTimeString();
            $start = $starthour;
            $end = $subhour;
            $hours--; // Subtract an hour from hours when looping.

            $calls = \App\Sonus5kCDR::groupBy('disconnect_reason')
                ->select('disconnect_reason', DB::raw('count(*) as total'))
                ->whereBetween('start_time', [$start, $end])
                ->where('type', 'ATTEMPT')
                ->get();

            $totalcalls = \App\Sonus5kCDR::whereBetween('start_time', [$start, $end])->count();

            $pktlosscalls = Sonus5kCDR::whereBetween('start_time', [$start, $end])
                                ->where(function ($query) {
                                    $query->where('ingress_lost_ptks', '>', 100)
                                    ->orWhere('egress_lost_ptks', '>', 100);
                                })
                                ->get();
            $losscalls = [];
            foreach ($pktlosscalls as $call) {
                $losscalls = [];
                    /*
                    if ($call['call_duration']) {
                        $call['call_duration'] = gmdate('H:i:s', ($call['call_duration'] * 10) / 1000);
                    }

                    //$call['disconnect_initiator_desc'] = Sonus5kCDR::get_disconnect_initiator_code($call['disconnect_initiator']);
                    //$call['disconnect_reason_desc'] = Sonus5kCDR::get_call_termination_code($call['disconnect_reason']);
                    */
                    $ingress_pkt_loss = $call['cdr_json']['Ingress Number of Packets Recorded as Lost'];
                $ingress_pkts_recieved = $call['cdr_json']['Ingress Number of Audio Packets Received'];
                $ingress_pkt_loss_percent = $ingress_pkt_loss / ($ingress_pkts_recieved + $ingress_pkt_loss) * 100;
                $ingress_pkt_loss_percent = round($ingress_pkt_loss_percent, 2, PHP_ROUND_HALF_UP);
                $call['ingress_pkt_loss_percent'] = $ingress_pkt_loss_percent;

                $egress_pkt_loss = $call['cdr_json']['Egress Number of Packets Recorded as Lost'];
                $egress_pkts_recieved = $call['cdr_json']['Egress Number of Audio Packets Received'];
                $egress_pkt_loss_percent = $egress_pkt_loss / ($egress_pkts_recieved + $egress_pkt_loss) * 100;
                $egress_pkt_loss_percent = round($egress_pkt_loss_percent, 2, PHP_ROUND_HALF_UP);
                $call['egress_pkt_loss_percent'] = $egress_pkt_loss_percent;

                    //return $call;
                    if ($ingress_pkt_loss_percent > 1 || $egress_pkt_loss_percent > 1) {
                        $losscalls[] = $call;
                    }
            }

            $pktlosscalls = array_reverse($losscalls);
            $pktlosscalls = count($pktlosscalls);

            $codes_inuse = [];

            foreach ($codes as $code) {
                // Resolve the code to description.
                $code = $code['disconnect_reason'].' - '.Sonus5kCDR::get_call_termination_code($code['disconnect_reason']);

                // Set default value of 0 for all inuse code for each interval.
                $codes_inuse['totalCalls'] = 0;
                $codes_inuse['packetLoss'] = 0;
                $codes_inuse[$code] = 0;
            }

            $attempt_count = $codes_inuse;

            // set the value for each disconnect type in time window.
            foreach ($calls as $i) {
                $attempt_count['totalCalls'] = $totalcalls;
                $attempt_count['packetLoss'] = $pktlosscalls;
                $attempt_count[$i->disconnect_reason.' - '.Sonus5kCDR::get_call_termination_code($i->disconnect_reason)] = $i->total;
            }

            // Append to the return array with the end time as the key.
            $return[$end] = $attempt_count;
        }

        $response = [
                    'status_code'          => 200,
                    'success'              => true,
                    'message'              => '',
                    'request'              => $request->all(),
                    'count'                => count($return),
                    'result'               => $return,
                    ];

        return response()->json($response);
    }

    public function get_call_termination_code(Request $request, $code)
    {
        // Resolve the Code to Description
        return Sonus5kCDR::get_call_termination_code($code);
    }

    public function get_disconnect_initiator_code(Request $request, $code)
    {
        // Resolve the Code to Description
        return Sonus5kCDR::get_disconnect_initiator_code($code);
    }

    public function get_last_two_days_cdr_completed_call_summary(Request $request)
    {
        // Real time from SBC
        $RETURN = [];
        foreach ($this->SBCS as $SBC) {
            $RETURN[$SBC] = Sonus5kCDR::get_travis_view(Sonus5kCDR::parse_cdr(Sonus5kCDR::get_last_two_days_cdr_completed_calls($SBC)));
        }

        return $RETURN;
    }

    public function get_last_two_days_cdr_completed_call_summary_packetloss(Request $request)
    {
        // Real time from SBC
        $RETURN = [];
        foreach ($this->SBCS as $SBC) {
            $RETURN[$SBC] = [];
            $RECORDS = Sonus5kCDR::get_travis_view(Sonus5kCDR::parse_cdr(Sonus5kCDR::get_last_two_days_cdr_completed_calls($SBC)));
            foreach ($RECORDS as $RECORD) {
                if ($RECORD['Media Stream Stats']['Ingress Packet Lost 1'] > 100 || $RECORD['Media Stream Stats']['Egress Packet Lost 1'] > 100) {
                    $RETURN[$SBC][] = $RECORD;
                }
            }
        }

        return $RETURN;
    }

    public function get_last_two_days_cdr_summary(Request $request)
    {
        // Real time from SBC
        $RETURN = [];
        foreach ($this->SBCS as $SBC) {
            $RETURN[$SBC] = Sonus5kCDR::get_travis_view(Sonus5kCDR::parse_cdr(Sonus5kCDR::get_last_two_days_cdrs($SBC)));
        }

        return $RETURN;
    }

    public function getcdrs(Request $request)
    {
        // Real time from SBC
        foreach ($this->SBCS as $SBC) {

            // Get latest CDR File.
            $location = Sonus5kCDR::get_cdr_log_names($SBC);

            $location = $location[0];
            //print_r($location);

            $sftp = new Net_SFTP($SBC, 2024);
            if (! $sftp->login(env('SONUSSFTPUSER'), env('SONUSSFTPPASS'))) {
                exit('Login Failed');
            }

            $currentfile = $sftp->get($location);

            $currentfile = explode(PHP_EOL, $currentfile);
            array_shift($currentfile);
            //return $currentfilelines;

            //return count($currentfile);

            $lastcall = count($currentfile);
            $last500call = $lastcall - 500;

            $last500calls = array_slice($currentfile, $last500call, $lastcall);
            array_pop($last500calls);
            //return $last500calls;

            $callrecords = [];
            $last500calls = implode(PHP_EOL, $last500calls);

            $last500calls = Sonus5kCDR::parse_cdr($last500calls);

            $return = [];
            foreach ($last500calls as $line) {
                //$line = explode(",", $line);
                $record = array_pop($last500calls);
                //return $record;
                //return $line;

                //array_shift($currentfile);

                $callrecords[] = $record;
            }

            //return $callrecords;

            return Sonus5kCDR::get_travis_view($callrecords);

            // copies filename.remote to filename.local from the SFTP server
            $sftp->get($location, $localdir.'/'.$filename);

            $files = scandir($localdir);
            print_r($files);
        }
    }
}
