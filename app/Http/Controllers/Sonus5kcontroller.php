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

class Sonus5kcontroller extends Controller
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

    public function listactivecalls(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        // Check user permissions
        if (! $user->can('read', Sonus5k::class)) {
            abort(401, 'You are not authorized');
        }

        // Name of Cache key.
        $key = 'listactivecalls';

        // Check if calls exist in cache. If not then move on.
        if (Cache::has($key)) {
            //Log::info(__METHOD__.' Used Cache');
            return Cache::get($key);
        }
        //Log::info(__METHOD__.' Did not Use Cache');
        $CALLS = [];
        foreach ($this->SBCS as $SBC) {
            $CALLS[$SBC] = Sonus5k::listactivecalls($SBC);
        }

        // Cache Calls for 5 seconds - Put the $CALLS as value of cache.
        $time = Carbon::now()->addSeconds(7);
        Cache::put($key, $CALLS, $time);

        return $CALLS;
    }

    public function listcallDetailStatus(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        // Check user permissions
        if (! $user->can('read', Sonus5k::class)) {
            abort(401, 'You are not authorized');
        }

        // Name of Cache key.
        $key = 'listcallDetailStatus';

        // Check if calls exist in cache. If not then move on.
        if (Cache::has($key)) {
            //Log::info(__METHOD__.' Used Cache');
            return Cache::get($key);
        }
        //Log::info(__METHOD__.' Did not Use Cache');
        $CALLS = [];
        foreach ($this->SBCS as $SBC) {
            $calls = Sonus5k::listcallDetailStatus($SBC);

            $return = [];
            $calls = (array) $calls['sonusActiveCall:callDetailStatus'];
            //return $calls;
            foreach ($calls as $call) {

                // Convert seconds into readable format Hours, minutes, seconds.
                $call['callDuration'] = gmdate('H:i:s', ($call['callDuration']));
                $return[] = $call;
            }

            $CALLS[$SBC] = $return;
        }

        // Cache Calls for 5 seconds - Put the $CALLS as value of cache.
        $time = Carbon::now()->addSeconds(7);
        Cache::put($key, $CALLS, $time);

        return $CALLS;
    }

    public function indexAssocByKey($stuff, $key)
    {
        $result = [];
        foreach ($stuff as $thing) {
            $index = $thing[$key];
            if (! $index) {
                throw new \Exception('error sorting arrya by key, key was missing from thing');
            }
            $result[$index] = $thing;
        }

        return $result;
    }

    public function merge_calldetails_with_mediadetails($request)
    {

         // Name of Cache key.
        $cache_key = 'merge_calldetails_with_mediadetails';

        // Check if calls exist in cache. If not then move on.
        if (Cache::has($cache_key) && ! $request->get('nocache')) {
            //Log::info(__METHOD__.' Used Cache');
            //print "returned cache".PHP_EOL;
            return Cache::get($cache_key);
        } else {
            //Log::info(__METHOD__.' Did not Use Cache');
            $RETURN = [];
            foreach ($this->SBCS as $SBC) {
                $RETURN[$SBC] = null;
                $callsMedia = [];
                $calls = Sonus5k::listcallDetailStatus($SBC);
                $media = Sonus5k::listcallMediaStatus($SBC);

                $calls = (array) $calls['sonusActiveCall:callDetailStatus'];
                $media = (array) $media['sonusActiveCall:callMediaStatus'];

                $indexedCalls = $this->indexAssocByKey($calls, 'GCID');
                $indexedMedia = $this->indexAssocByKey($media, 'GCID');
                //return $indexedMedia;
                foreach ($indexedCalls as $key => $value) {

                    // Merge our Call Details and our Media Details into a single array for each GCID.
                    $callsMedia[$key] = array_merge($indexedCalls[$key], $indexedMedia[$key]);
                }
                // Add the original key back on so the UI doesn't get jacked up.
                if ($callsMedia) {
                    $calls = [];
                    foreach ($callsMedia as $call) {

                        // Convert seconds into readable format Hours, minutes, seconds.
                        $call['callDuration'] = gmdate('H:i:s', ($call['callDuration']));

                        $ingress_pkt_loss = $call['ingressMediaStream1PacketsLost'];
                        $ingress_pkts_recieved = $call['ingressMediaStream1PacketsReceived'];
                        if ($ingress_pkts_recieved + $ingress_pkt_loss) {
                            $ingress_pkt_loss_percent = $ingress_pkt_loss / ($ingress_pkts_recieved + $ingress_pkt_loss) * 100;
                            $ingress_pkt_loss_percent = round($ingress_pkt_loss_percent, 2, PHP_ROUND_HALF_UP);
                            $call['ingress_pkt_loss_percent'] = $ingress_pkt_loss_percent;
                        } else {
                            $call['ingress_pkt_loss_percent'] = 0;
                        }

                        $egress_pkts_recieved = $call['egressMediaStream1PacketsReceived'];
                        $egress_pkt_loss = $call['egressMediaStream1PacketsLost'];
                        if ($egress_pkts_recieved + $egress_pkt_loss) {
                            $egress_pkt_loss_percent = $egress_pkt_loss / ($egress_pkts_recieved + $egress_pkt_loss) * 100;
                            $egress_pkt_loss_percent = round($egress_pkt_loss_percent, 2, PHP_ROUND_HALF_UP);
                            $call['egress_pkt_loss_percent'] = $egress_pkt_loss_percent;
                        } else {
                            $call['egress_pkt_loss_percent'] = 0;
                        }

                        $calls[] = $call;
                    }

                    $RETURN[$SBC]['sonusActiveCall:callSummaryStatus_Media'] = $calls;
                }
            }

            // Cache Calls for 15 seconds - Put the $CALLS as value of cache.
            $time = Carbon::now()->addSeconds(10);
            Cache::put($cache_key, $RETURN, $time);

            return $RETURN;
        }
    }

    public function listcallDetailStatus_Media(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        // Check user permissions
        if (! $user->can('read', Sonus5k::class)) {
            abort(401, 'You are not authorized');
        }

        $RETURN = $this->merge_calldetails_with_mediadetails($request);

        return $RETURN;
    }

    public function listcallMediaStatus(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        // Check user permissions
        if (! $user->can('read', Sonus5k::class)) {
            abort(401, 'You are not authorized');
        }

        // Name of Cache key.
        $key = 'listcallMediaStatus';

        // Check if calls exist in cache. If not then move on.
        if (Cache::has($key)) {
            //Log::info(__METHOD__.' Used Cache');
            return Cache::get($key);
        }
        //Log::info(__METHOD__.' Did not Use Cache');
        $CALLS = [];
        foreach ($this->SBCS as $SBC) {
            $CALLS[$SBC] = Sonus5k::listcallMediaStatus($SBC);
        }

        // Cache Calls for 5 seconds - Put the $CALLS as value of cache.
        $time = Carbon::now()->addSeconds(5);
        Cache::put($key, $CALLS, $time);

        return $CALLS;
    }

    public function listactivealarms(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        // Check user permissions
        if (! $user->can('read', Sonus5k::class)) {
            abort(401, 'You are not authorized');
        }

        // Name of Cache key.
        $key = 'listactivealarms';

        // Check if calls exist in cache. If not then move on.
        if (Cache::has($key)) {
            //Log::info(__METHOD__.' Used Cache');
            return Cache::get($key);
        }

        $CALLS = [];
        foreach ($this->SBCS as $SBC) {
            $CALLS[$SBC] = Sonus5k::listactivealarms($SBC);
        }

        // Cache Calls for 5 seconds - Put the $CALLS as value of cache.
        $time = Carbon::now()->addSeconds(60);
        Cache::put($key, $CALLS, $time);

        return $CALLS;
    }

    public function sftpdownload($server, $location)
    {
        $localdir = __DIR__.'/tmp';
        $files = scandir($localdir);
        print_r($files);

        $sftp = new Net_SFTP($server.':2024');
        if (! $sftp->login(env('SONUSSFTPUSER'), env('SONUSSFTPPASS'))) {
            exit('Login Failed');
        }

        // outputs the contents of filename.remote to the screen
        //echo $sftp->get($location);
        // copies filename.remote to filename.local from the SFTP server
        $sftp->get($location, "{$localdir}/{$server}");

        $files = scandir($localdir);
        print_r($files);
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

    // Real time from SBC
    public function getcdrs(Request $request)
    {
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
