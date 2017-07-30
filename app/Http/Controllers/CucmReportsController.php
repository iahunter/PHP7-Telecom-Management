<?php

namespace App\Http\Controllers;

use DB;
use App\Cucmsiteconfigs;
use App\Cucmphoneconfigs;
use Illuminate\Http\Request;
// Include the JWT Facades shortcut
use Tymon\JWTAuth\Facades\JWTAuth;

//use Dingo\Api\Routing\Helpers;

class CucmReportsController extends Controller
{
    public $phones = [];
	public $extnlength = [];

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
		
		// Get extension Length from phone descriptions
		
		/* Moved this code to the hourly site scan command and updates the database table. 
		
        $phone_array = [];

        if ($phonecount) {
            $phone_array[] = Cucmphoneconfigs::where('devicepool', 'like', '%'.$request->sitecode.'%')->chunk(300, function ($phones) {
                
                foreach ($phones as $phone) {
                    $desc_array = explode(" - ", $phone['description']);
					
                    if(is_array($desc_array)){
						$shortextn = array_pop($desc_array);
						$shortextnlength = strlen($shortextn);
						if(isset($this->extnlength[$shortextnlength])){
							$this->extnlength[$shortextnlength] = $this->extnlength[$shortextnlength] + 1;
						}else{
							$this->extnlength[$shortextnlength] = 1;
						}
						
					}
                }
            });
			
			//print_r($this->extnlength);
			
			// Get the one with the most counts. 
			$extnlength = array_keys($this->extnlength, max($this->extnlength));
			$extnlength = $extnlength[0];
			$site->shortextenlength = $extnlength;	
        }else{
			$site->shortextenlength = 4;
		}
		
		*/

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
        SELECT cucmsite.sitecode, cucmsite.trunking, cucmsite.e911,
        COUNT(cucmphone.id) as phonecount
        FROM cucmsite
        LEFT JOIN cucmphone ON SUBSTRING(cucmphone.devicepool, 4) = cucmsite.sitecode AND cucmphone.deleted_at is NULL
        WHERE cucmsite.deleted_at is NULL
        GROUP BY devicepool, cucmsite.sitecode, cucmsite.trunking, cucmsite.e911
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

        foreach ($models as $model) {
            //$phone_models[] = $model->model;

            // Strip "Cisco " out of model name.
            $phone_models[] = str_replace('Cisco ', '', $model->model);
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
}
