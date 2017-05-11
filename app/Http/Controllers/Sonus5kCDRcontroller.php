<?php

namespace App\Http\Controllers;

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

    public function list_todays_calls_with_loss(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        // Check Role of user
        if (! $user->can('read', Sonus5kCDR::class)) {
            abort(401, 'You are not authorized');
        }

        // Add 6 hrs to compensate for the timestamps in
        $start = Carbon::today()->addHours(6)->toDateTimeString();
        $end = Carbon::tomorrow()->addHours(6)->toDateTimeString();

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

    public function list_calls_by_date_range_with_loss(Request $request)
    {
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
