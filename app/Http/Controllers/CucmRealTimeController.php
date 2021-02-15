<?php

namespace App\Http\Controllers;

use App\Cucmclass;
use App\CucmRealTime;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

// CUCM Real Time API Testing

class CucmRealTimeController extends Controller
{
    public function __construct()
    {
        // Only authenticated users can make these calls
        $this->middleware('jwt.auth');

        // Construct new cucm object
        $this->CucmRealTime = new CucmRealTime();
    }

    public function get_phone_ip(Request $request)
    {
        // First test for RIS API. Used to get IP for the Phone.

        $user = JWTAuth::parseToken()->authenticate();
        // Check user permissions
        if (! $user->can('read', Cucmclass::class)) {
            abort(401, 'You are not authorized');
        }

        // Get name from $request;
        $name = $request->name;

        $count = 0;

        // Search for name.
        $searchCriteria["SelectItem[$count]"]['Item'] = $name;

        $result = $this->CucmRealTime->getIPAddresses($searchCriteria);

        $response = [
            'status_code'    => 200,
            'success'        => true,
            'message'        => '',
            'response'       => $result,
        ];

        return response()->json($response);
    }
}
