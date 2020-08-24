<?php

namespace App\Http\Controllers;

use App\Cucmclass;	// Cache
// Add Dummy CUCM class for permissions use for now.
use App\Gizmo\RestApiClient as Gizmo;
use App\PhoneMACD;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Tymon\JWTAuth\Facades\JWTAuth;

class GizmoController extends Controller
{
    public function __construct()
    {
        // Only authenticated users can make these calls
        $this->middleware('jwt.auth');

        // Construct new cucm object
        $this->client = new Gizmo(env('MICROSOFT_TENANT'), env('GIZMO_URL'), env('GIZMO_CLIENT_ID'), env('GIZMO_CLIENT_SECRET'), env('GIZMO_SCOPE'));
    }

    public function getTeamsUserbyNumber(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        // Check user permissions
        if (! $user->can('read', Cucmclass::class)) {
            if (! $user->can('read', PhoneMACD::class)) {
                abort(401, 'You are not authorized');
            }
        }

        $number = $request->number;
        $countrycode = $request->countrycode;

        $this->client->get_oauth2_token();

        $NPANXX = "{$countrycode}{$number}";

        try {
            $teamsuser = $this->client->get_teams_csonline_all_users_by_NPA_NXX($NPANXX);

            if (! count($phone)) {
                throw new \Exception('Indexed results from call mangler is empty');
            }
        } catch (\Exception $e) {
            $exception = 'Teams blew up: '.$e->getMessage().PHP_EOL;
            //dd($e->getTrace());
        }

        $response = [
            'status_code'    => 200,
            'success'        => true,
            'message'        => '',
            'response'       => $teamsuser,
        ];

        return response()->json($response);
    }
}
