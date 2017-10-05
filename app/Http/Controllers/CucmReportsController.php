<?php

namespace App\Http\Controllers;

use DB;
use App\Did;
use App\Cucmsiteconfigs;
use App\Cucmphoneconfigs;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
// Include the JWT Facades shortcut
use Illuminate\Support\Facades\Storage;

//use Dingo\Api\Routing\Helpers;

class CucmReportsController extends Controller
{
    public $phones = [];
    public $extnlength = [];
    public $sitecode;

    //use Helpers;
    public function __construct()
    {
        // Only authenticated users can make these calls
        $this->middleware('jwt.auth');
    }

    public function sitesSummary()
    {
        $user = JWTAuth::parseToken()->authenticate();

        if (! $user->can('read', Cucmsiteconfigs::class)) {
            abort(401, 'You are not authorized');
        }

        $sites = Cucmsiteconfigs::all();

        $response = [
                    'status_code'       => 200,
                    'success'           => true,
                    'message'           => '',
                    'response'          => $sites,
                    ];

        return response()->json($response);
    }

    public function siteSummary(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        if (! $user->can('read', Cucmsiteconfigs::class)) {
            abort(401, 'You are not authorized');
        }

        $site = Cucmsiteconfigs::where('sitecode', $request->sitecode)->first();
        $phonecount = Cucmphoneconfigs::where('devicepool', 'like', '%'.$request->sitecode.'%')->count();

        // Change Site type based on site design user chooses. This will determine the site type.
        if ($site->trunking == 'sip' && $site->e911 == '911enable') {
            $site->type = 1;
        } elseif ($site->trunking == 'local' && $site->e911 == '911enable') {
            $site->type = 2;
        } elseif ($site->trunking == 'sip' && $site->e911 == 'local') {
            $site->type = 3;
        } elseif ($site->trunking == 'local' && $site->e911 == 'local') {
            $site->type = 4;
        }

        $site->phonecount = $phonecount;
        //print_r($site);

        $response = [
                    'status_code'       => 200,
                    'success'           => true,
                    'message'           => '',
                    'response'          => $site,
                    ];

        return response()->json($response);
    }

    public function get_phone_by_name(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        if (! $user->can('read', Cucmsiteconfigs::class)) {
            abort(401, 'You are not authorized');
        }

        $name = $request->name;

        $count = Cucmphoneconfigs::where('name', '=', $name)->count();
        //return $count;
        if ($count) {
            $phones = Cucmphoneconfigs::where('name', '=', $name)->get();

            foreach ($phones as $phone) {
                $phone = $phone;
            }
        } else {
            $phone = null;
        }

        $response = [
                    'status_code'          => 200,
                    'success'              => true,
                    'message'              => '',
                    'response'             => $phone,

                    ];

        return response()->json($response);
    }

    public function get_phones_like_erl(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        if (! $user->can('read', Cucmsiteconfigs::class)) {
            abort(401, 'You are not authorized');
        }

        $count = Cucmphoneconfigs::where('erl', 'like', '%'.$request->erl.'%')->count();

        $phone_array = [];

        if ($count) {
            $phone_array[] = Cucmphoneconfigs::where('erl', 'like', '%'.$request->erl.'%')->chunk(300, function ($phones) {
                $return = [];
                foreach ($phones as $phone) {
                    //print_r($phone);
                    $lines = [];
                    foreach ($phone['lines'] as $line) {
                        $ln = [];
                        $ln['uuid'] = $line['uuid'];
                        $ln['pattern'] = $line['pattern'];
                        $ln['description'] = $line['description'];
                        $ln['callForwardAll'] = [];
                        $ln['callForwardAll']['destination'] = $line['callForwardAll']['destination'];
                        $ln['css'] = $line['shareLineAppearanceCssName']['_'];
                        $lines[$ln['uuid']] = $ln;
                    }
                    $phone->lines = $lines;        // replace the lines with only the fields we need for our UI.
                    $phone->config = '';        // Scrap the config, we dont' need it.

                    $this->phones[] = $phone;    // Append the phone to the array to return.
                }
            });
        }

        //return ($this->phones);

        /*
        if ($count) {
            $phones = Cucmphoneconfigs::where('devicepool', 'like', '%'.$request->sitecode.'%')->get();
        }
        */

        $response = [
                    'status_code'       => 200,
                    'success'           => true,
                    'message'           => '',
                    'count'             => $count,
                    'response'          => $this->phones,
                    ];

        return response()->json($response);
    }
	
	public function get_devicepool_from_phones_in_erl(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        if (! $user->can('read', Cucmsiteconfigs::class)) {
            abort(401, 'You are not authorized');
        }
		
		$ERL = $request->erl;
		
		/*
		SELECT devicepool, COUNT(*) as count
		FROM cucmphone
		WHERE erl LIKE 'SHDNEAKV%'
		AND deleted_at IS NULL
		GROUP BY devicepool
		*/

		
		$DPs = DB::select(DB::raw("SELECT devicepool, COUNT(*) as count FROM cucmphone WHERE erl LIKE '$ERL%' AND deleted_at IS NULL GROUP BY devicepool"));
		
		if($DPs){
			$count = 0;
			foreach($DPs as $DP){
				if ($DP->count > $count){
					$count = $DP->count; 
					$devicepool = $DP->devicepool; 
				}
			}
			
			$SITE = str_replace("DP_", "", $devicepool);
		
		}else{
			$SITE = null;
		}
		
		
		
		
        $response = [
                    'status_code'       => 200,
                    'success'           => true,
                    'message'           => '',
                    'response'          => $SITE,
                    ];

        return response()->json($response);
    }

    public function get_phones_by_erl(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        if (! $user->can('read', Cucmsiteconfigs::class)) {
            abort(401, 'You are not authorized');
        }

        $count = Cucmphoneconfigs::where('erl', '=', $request->erl)->count();

        $phone_array = [];

        if ($count) {
            $phone_array[] = Cucmphoneconfigs::where('erl', '=', $request->erl)->chunk(300, function ($phones) {
                $return = [];
                foreach ($phones as $phone) {
                    //print_r($phone);
                    $lines = [];
                    foreach ($phone['lines'] as $line) {
                        $ln = [];
                        $ln['uuid'] = $line['uuid'];
                        $ln['pattern'] = $line['pattern'];
                        $ln['description'] = $line['description'];
                        $ln['callForwardAll'] = [];
                        $ln['callForwardAll']['destination'] = $line['callForwardAll']['destination'];
                        $ln['css'] = $line['shareLineAppearanceCssName']['_'];
                        $lines[$ln['uuid']] = $ln;
                    }
                    $phone->lines = $lines;        // replace the lines with only the fields we need for our UI.
                    $phone->config = '';        // Scrap the config, we dont' need it.

                    $this->phones[] = $phone;    // Append the phone to the array to return.
                }
            });
        }

        //return ($this->phones);

        /*
        if ($count) {
            $phones = Cucmphoneconfigs::where('devicepool', 'like', '%'.$request->sitecode.'%')->get();
        }
        */

        $response = [
                    'status_code'       => 200,
                    'success'           => true,
                    'message'           => '',
                    'count'             => $count,
                    'response'          => $this->phones,
                    ];

        return response()->json($response);
    }

    public function phones_in_site_erl_but_not_in_site_config(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        if (! $user->can('read', Cucmsiteconfigs::class)) {
            abort(401, 'You are not authorized');
        }

        $this->sitecode = $request->sitecode;

        //return $this->sitecode;

        $count = Cucmphoneconfigs::where('erl', 'like', '%'.$this->sitecode.'%')->count();
        //return $count;
        if ($count) {
            $phone_array[] = Cucmphoneconfigs::where('erl', 'like', '%'.$this->sitecode.'%')->chunk(300, function ($phones) {
                $return = [];
                foreach ($phones as $phone) {
                    //print_r($phone);
                    $REGEX = "/{$this->sitecode}/";
                    if (! preg_match($REGEX, $phone->devicepool)) {

                        //$this->phones[] = $phone;

                        //print_r($phone);
                        $lines = [];
                        foreach ($phone['lines'] as $line) {
                            $ln = [];
                            $ln['uuid'] = $line['uuid'];
                            $ln['pattern'] = $line['pattern'];
                            $ln['description'] = $line['description'];
                            $ln['callForwardAll'] = [];
                            $ln['callForwardAll']['destination'] = $line['callForwardAll']['destination'];
                            $ln['css'] = $line['shareLineAppearanceCssName']['_'];
                            $lines[$ln['uuid']] = $ln;
                        }
                        $phone->lines = $lines;        // replace the lines with only the fields we need for our UI.
                        $phone->config = '';        // Scrap the config, we dont' need it.

                        $this->phones[] = $phone;    // Append the phone to the array to return.
                    }
                }
                //return $this->phones;
            });
        }

        $response = [
                    'status_code'          => 200,
                    'success'              => true,
                    'message'              => '',
                    'response'             => $this->phones,

                    ];

        return response()->json($response);
    }

    public function sitePhones(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        if (! $user->can('read', Cucmsiteconfigs::class)) {
            abort(401, 'You are not authorized');
        }

        $count = Cucmphoneconfigs::where('devicepool', 'like', '%'.$request->sitecode.'%')->count();

        $phone_array = [];

        if ($count) {
            $phone_array[] = Cucmphoneconfigs::where('devicepool', 'like', '%'.$request->sitecode.'%')->chunk(300, function ($phones) {
                $return = [];
                foreach ($phones as $phone) {
                    //print_r($phone);
                    $lines = [];
                    foreach ($phone['lines'] as $line) {
                        $ln = [];
                        $ln['uuid'] = $line['uuid'];
                        $ln['pattern'] = $line['pattern'];
                        $ln['description'] = $line['description'];
                        $ln['callForwardAll'] = [];
                        $ln['callForwardAll']['destination'] = $line['callForwardAll']['destination'];
                        $ln['css'] = $line['shareLineAppearanceCssName']['_'];
                        $lines[$ln['uuid']] = $ln;
                    }
                    $phone->lines = $lines;        // replace the lines with only the fields we need for our UI.
                    $phone->config = '';        // Scrap the config, we dont' need it.

                    $this->phones[] = $phone;    // Append the phone to the array to return.
                }
            });
        }

        //return ($this->phones);

        /*
        if ($count) {
            $phones = Cucmphoneconfigs::where('devicepool', 'like', '%'.$request->sitecode.'%')->get();
        }
        */

        $response = [
                    'status_code'       => 200,
                    'success'           => true,
                    'message'           => '',
                    'count'             => $count,
                    //'response'          => $phones,
                    'response'          => $this->phones,
                    ];

        return response()->json($response);
    }

    public function siteE911TrunkingReport()
    {
        $user = JWTAuth::parseToken()->authenticate();

        if (! $user->can('read', Cucmsiteconfigs::class)) {
            abort(401, 'You are not authorized');
        }

        /*
        // Custom SQL Query for Report
        SELECT cucmsite.sitecode, cucmsite.trunking, cucmsite.e911, cucmsite.shortextenlength,
        COUNT(cucmphone.id) as phonecount
        FROM cucmsite
        LEFT JOIN cucmphone ON SUBSTRING(cucmphone.devicepool, 4) = cucmsite.sitecode AND cucmphone.deleted_at is NULL
        WHERE cucmsite.deleted_at is NULL
        GROUP BY devicepool, cucmsite.sitecode, cucmsite.trunking, cucmsite.e911, cucmsite.shortextenlength
        ORDER BY cucmsite.sitecode
        */

        $sites = DB::select(DB::raw('SELECT cucmsite.sitecode, cucmsite.trunking, cucmsite.e911, cucmsite.shortextenlength, COUNT(cucmphone.id) as phonecount FROM cucmsite  LEFT JOIN cucmphone ON SUBSTRING(cucmphone.devicepool, 4) = cucmsite.sitecode AND cucmphone.deleted_at is NULL WHERE cucmsite.deleted_at is NULL GROUP BY devicepool, cucmsite.sitecode, cucmsite.trunking, cucmsite.e911, cucmsite.shortextenlength ORDER BY cucmsite.sitecode'));

        // Get Trunking for Graph
        $trunking = DB::table('cucmsite')
            ->select('cucmsite.trunking', (DB::raw('count(cucmsite.trunking) as count')))
            ->where('deleted_at', '=', null)
            ->groupBy('trunking')
            ->get();

        $trunkcount = [];
        foreach ($trunking as $i) {
            $trunkcount[$i->trunking] = $i->count;
        }

        // Get E911 for Graph
        $e911 = DB::table('cucmsite')
            ->select('cucmsite.e911', (DB::raw('count(cucmsite.e911) as count')))
            ->where('deleted_at', '=', null)
            ->groupBy('e911')
            ->get();

        $e911count = [];
        foreach ($e911 as $i) {
            $e911count[$i->e911] = $i->count;
        }

        $response = [
                    'status_code'          => 200,
                    'success'              => true,
                    'message'              => '',
                    'trunkingstats'        => $trunkcount,
                    'e911stats'            => $e911count,
                    'response'             => $sites,

                    ];

        return response()->json($response);
    }

    public function siteE911TrunkingReport_oldandslow()
    {
        // No longer used but keeping around for some reference.
        $user = JWTAuth::parseToken()->authenticate();

        if (! $user->can('read', Cucmsiteconfigs::class)) {
            abort(401, 'You are not authorized');
        }

        //$sites = Cucmsiteconfigs::find(array('sitecode', 'trunking', 'e911'));

        // Get Site Phone Count and append it to the site object for report.

        //$sites = DB::table('cucmsite')->where('deleted_at', '=', null)->select('sitecode', 'trunking', 'e911')->orderBy('sitecode')->get();

        $sites = [];

        foreach (DB::table('cucmsite')->where('deleted_at', '=', null)->select('sitecode', 'trunking', 'e911')->orderBy('sitecode')->cursor() as $site) {
            $phones = Cucmphoneconfigs::where('devicepool', 'like', '%'.$site->sitecode.'%')->count();
            $site->phonecount = $phones;
            $sites[] = $site;
        }
        // End of Site Phone Counts.

        $trunking = DB::table('cucmsite')
            ->select('cucmsite.trunking', (DB::raw('count(cucmsite.trunking) as count')))
            ->where('deleted_at', '=', null)
            ->groupBy('trunking')
            ->get();

        $trunkcount = [];
        foreach ($trunking as $i) {
            $trunkcount[$i->trunking] = $i->count;
        }

        $e911 = DB::table('cucmsite')
            ->select('cucmsite.e911', (DB::raw('count(cucmsite.e911) as count')))
            ->where('deleted_at', '=', null)
            ->groupBy('e911')
            ->get();

        $e911count = [];
        foreach ($e911 as $i) {
            $e911count[$i->e911] = $i->count;
        }

        //return $e911count;
        //return $sites;

        $response = [
                    'status_code'          => 200,
                    'success'              => true,
                    'message'              => '',
                    'trunkingstats'        => $trunkcount,
                    'e911stats'            => $e911count,
                    'response'             => $sites,

                    ];

        return response()->json($response);
    }

    public function get_phone_models_inuse()
    {
        $models = DB::table('cucmphone')
            ->select('cucmphone.model')
            ->groupBy('model')
            ->get();

        $phone_models = [];

        $exclude = [
                    'Analog Phone',
                    '30 VIP',
                    'Dual Mode for iPhone',
                    'ATA 186',
                    'Jabber for Tablet',
                    'TelePresence Codec C40',
                    'TelePresence EX60',
                    'Unified Client Services Framework',
                    'Unified Personal Communicator',
                    'CTI Port',
                    'Third-party SIP Device (Advanced)',
                    'Third-party SIP Device (Basic)',
                    ];

        foreach ($models as $model) {
            //$phone_models[] = $model->model;

            // Strip "Cisco " out of model name.
            $type = str_replace('Cisco ', '', $model->model);

            // Exclude types we don't want to built in MACD Tool
            if (in_array($type, $exclude)) {
                //print "EXCLUDE";
                continue;
            } else {
                $phone_models[] = $type;
            }
        }

        $response = [
                    'status_code'       => 200,
                    'success'           => true,
                    'message'           => '',
                    'response'          => $phone_models,
                    ];

        return response()->json($response);
    }

    public function get_count_phone_models_inuse()
    {
        $models = DB::table('cucmphone')
            ->select('cucmphone.model', DB::raw('count(cucmphone.model) as count'))
            ->groupBy('model')
            ->orderBy('count')
            ->get();

        $response = [
                    'status_code'       => 200,
                    'success'           => true,
                    'message'           => '',
                    'response'          => $models,
                    ];

        return response()->json($response);
    }

    public function get_line_cleanup_report()
    {
        /*
        // This was a bad way of doing this because you needed to know the apbolute path. Using the storage_path from laravel is better approach below.
        $location = "/PATHTOAPP/storage/cucm/linecleanup/report";

        if(!file_exists($location) || !is_readable($location)){
            return "FILE IS NOT BEING LOADED FROM: ". $location;
        }

        $json = file_get_contents($location);
        */

        if (! file_exists(storage_path('cucm/linecleanup/report.json')) || ! is_readable(storage_path('cucm/linecleanup/report.json'))) {
            return 'FILE IS NOT BEING LOADED FROM: '.$location;
        }

        // Get the json from the file in storage.
        $json = file_get_contents(storage_path('cucm/linecleanup/report.json'));

        $data = json_decode($json, true);

        $data = (array) $data;

        // Need look into using Storage vs file_get_contents
        //$data = Storage::disk('local')->get('marquee.json');

        $response = [
                    'status_code'       => 200,
                    'success'           => true,
                    'message'           => '',
                    'response'          => $data,
                    ];

        return response()->json($response);
    }

    public function get_count_phone_by_erl()
    {
        /*
        SELECT erl, count(erl)
        FROM `cucmphone`
        GROUP by erl
        */

        $models = DB::table('cucmphone')
            ->select('cucmphone.erl', DB::raw('count(cucmphone.erl) as count'))
            ->groupBy('erl')
            ->orderBy('count', 'DESC')
            ->get();

        $response = [
                    'status_code'       => 200,
                    'success'           => true,
                    'message'           => '',
                    'response'          => $models,
                    ];

        return response()->json($response);
    }
}
