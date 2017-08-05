<?php

namespace App\Http\Controllers;

// Add Dummy CUCM class for permissions use for now.
use App\Cucmclass;
use App\PhoneMACD;
use Illuminate\Http\Request;
use App\Events\Create_Phone_Event;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Events\Create_AD_IPPhone_Event;
use App\Events\Create_UnityConnection_Mailbox_Event;
use App\Events\Create_UnityConnection_LDAP_Import_Mailbox_Event;

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

        // Update AD User IP Phone Field
        if (isset($phone['username']) && $phone['username'] && (isset($phone['dn']) && $phone['dn'])) {
            $task = PhoneMACD::create(['type' => 'Update User AD IP Phone Field', 'status' => 'job recieved', 'form_data' => $phone, 'created_by' => $user->username]);

            $data['taskid'] = $task->id;

            // Testing of Events Controller
            event(new Create_AD_IPPhone_Event($data));
        }

        // Build Phone
        if (isset($phone['name']) && $phone['name']) {
            $task = PhoneMACD::create(['type' => 'Add Phone', 'status' => 'job recieved', 'form_data' => $phone, 'created_by' => $user->username]);

            $data['taskid'] = $task->id;

            // Testing of Events Controller
            event(new Create_Phone_Event($data));
        }

        // Build Voicemail Box
        if (isset($phone['voicemail'])) {
            if ($phone['voicemail'] == 'true') {
                if (isset($phone['template']) && $phone['template']) {
                    if (isset($phone['username']) && $phone['username']) {
                        $task = PhoneMACD::create(['type' => 'Create Mailbox from LDAP User', 'status' => 'job recieved', 'form_data' => $phone, 'created_by' => $user->username]);

                        $data['taskid'] = $task->id;

                        // Testing of Events Controller
                        event(new Create_UnityConnection_LDAP_Import_Mailbox_Event($data));
                    } else {
                        // If no username build user as a new user without Unified Messaging
                        $task = PhoneMACD::create(['type' => 'Create Mailbox with no UserID', 'status' => 'job recieved', 'form_data' => $phone, 'created_by' => $user->username]);

                        // Create the User Alias for the mailbox.
                        $data['phone']['username'] = $data['phone']['firstname'].' '.$data['phone']['lastname'].' '.$data['phone']['dn'];

                        $data['taskid'] = $task->id;

                        // Testing of Events Controller
                        event(new Create_UnityConnection_Mailbox_Event($data));
                    }
                }
            }
        }

        return $request;
    }

    public function Create_AD_IPPhone_Event($data)
    {
        event(new Create_AD_IPPhone_Event($data));
    }

    public function importPhoneMACD_Mailbox($data)
    {
        $phone = $request->all();

        // Testing of Events Controller
        event(new Create_UnityConnection_LDAP_Import_Mailbox_Event($data));

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
