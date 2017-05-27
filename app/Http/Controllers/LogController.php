<?php

namespace App\Http\Controllers;

use DB;
use Carbon\Carbon;
use Silber\Bouncer\Bouncer;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
// Activity Logger
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\Models\Activity;

class LogController extends Controller
{
    public function log_page_name(Request $request, $name)
    {
        $user = JWTAuth::parseToken()->authenticate();

        // Look for the page log syntax that we replace / with in the UI. Put the / back in.
        $name = explode('&', $name);

        $namearray = str_replace('~~~', '/', $name);
        $app = $namearray[0];
        $url = $namearray[1];
        //return $name;
        // Log activity in request
        activity('pagelog')->causedBy($user)->withProperties(['app' => $app, 'url' => $url])->log('Page Request');

        // Return Nothing
    }

    public function test(Request $request)
    {
        // Testing JSON inside of the .env file
        if (isset($_ENV['test'])) {
            $json = json_decode($_ENV['test'], true);

            return json_encode($json, true);
        } else {
            return 'Test Failed';
        }
    }

    public function permissions(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        // Check Role of user
        if (! $user->can('read', Activity::class)) {
            abort(401, 'You are not authorized');
        }

        //print_r($user);
        $abilities = $user->getAbilities();

        return $abilities;
    }

    public function get_logs_by_date_range(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        // Check Role of user
        if (! $user->can('read', Activity::class)) {
            abort(401, 'You are not authorized');
        }

        $calls = Activity::whereBetween('created_at', [$start, $end])->orderby('created_at')->get();

        return $calls;
    }

    public function get_last24hrs_logs(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        // Check Role of user
        if (! $user->can('read', Activity::class)) {
            abort(401, 'You are not authorized');
        }

        $start = Carbon::now()->subHours(24)->toDateTimeString();
        $end = Carbon::now()->toDateTimeString();

        $calls = Activity::whereBetween('created_at', [$start, $end])->orderby('created_at')->get();

        return $calls;
    }

    public function get_last24hrs_page_logs(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        // Check Role of user
        if (! $user->can('read', Activity::class)) {
            abort(401, 'You are not authorized');
        }

        $start = Carbon::now()->subHours(24)->toDateTimeString();
        $end = Carbon::now()->toDateTimeString();

        $calls = Activity::where('log_name', 'pagelog')
                ->whereBetween('created_at', [$start, $end])->orderby('created_at')->get();

        return $calls;
    }
}
