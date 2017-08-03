<?php

namespace App\Http\Controllers;

// Add Dummy CUCM class for permissions use for now.
use App\Cucmclass;
use App\PhoneMACD;
use App\QueuedTasks;
use Illuminate\Http\Request;
use App\Events\Create_Phone_Event;
use Tymon\JWTAuth\Facades\JWTAuth;

class PhoneMACDController extends Controller
{
    public function createPhoneMACD_Phone(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        // Check user permissions
        if (! $user->can('create', PhoneMACD::class)) {
            if (! $user->can('create', Cucmclass::class)) {
                abort(401, 'You are not authorized');
            }
        }

        $phone = $request->all();

        $data['phone'] = $phone;

        $task = QueuedTasks::create(['form_data' => $phone, 'created_by' => $user->username]);

        $data['taskid'] = $task->id;

        // Testing of Events Controller
        event(new Create_Phone_Event($data));

        return $request;
    }

    public function importPhoneMACD_Mailbox(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        // Check user permissions
        if (! $user->can('create', PhoneMACD::class)) {
            if (! $user->can('create', Cucmclass::class)) {
                abort(401, 'You are not authorized');
            }
        }

        $phone = $request->all();

        // Testing of Events Controller
        event(new Create_UnityConnection_Mailbox_Event($phone));

        return $request;
    }

    public function createPhoneMACD_Mailbox(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        // Check user permissions
        if (! $user->can('create', PhoneMACD::class)) {
            if (! $user->can('create', Cucmclass::class)) {
                abort(401, 'You are not authorized');
            }
        }

        $phone = $request->all();

        // Testing of Events Controller
        event(new Create_UnityConnection_Mailbox_Event($phone));

        return $request;
    }

    public function createPhoneMACD_AD_IPPhone(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        // Check user permissions
        if (! $user->can('create', PhoneMACD::class)) {
            if (! $user->can('create', Cucmclass::class)) {
                abort(401, 'You are not authorized');
            }
        }

        $phone = $request->all();

        // Testing of Events Controller
        event(new Create_AD_IPPhone_Event($phone));

        return $request;
    }
}
