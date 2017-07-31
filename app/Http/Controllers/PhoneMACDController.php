<?php

namespace App\Http\Controllers;

// Add Dummy CUCM class for permissions use for now.
use App\Cucmclass;
use App\PhoneMACD;
use Illuminate\Http\Request;
use App\Events\Create_Phone_Event;
use Tymon\JWTAuth\Facades\JWTAuth;

class PhoneMACDController extends Controller
{
    public function createPhoneMACD_Phone(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        // Check user permissions
        if (! $user->can('create', Cucmclass::class)) {
            abort(401, 'You are not authorized');
        }

        $phone = $request->all();

        // Testing of Events Controller
        event(new Create_Phone_Event($phone));

        return $request;
    }
}
