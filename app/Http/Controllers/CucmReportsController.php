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

        $sites = Cucmsiteconfigs::where('sitecode', $request->sitecode)->get();

        $response = [
                    'status_code'       => 200,
                    'success'           => true,
                    'message'           => '',
                    'response'          => $sites,
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

        if ($count) {
            $phones = Cucmphoneconfigs::where('devicepool', 'like', '%'.$request->sitecode.'%')->get();
        }

        $response = [
                    'status_code'       => 200,
                    'success'           => true,
                    'message'           => '',
                    'count'             => $count,
                    'response'          => $phones,
                    ];

        return response()->json($response);
    }

    public function siteE911TrunkingReport()
    {
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
