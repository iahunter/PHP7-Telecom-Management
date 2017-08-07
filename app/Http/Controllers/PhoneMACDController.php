<?php

namespace App\Http\Controllers;

// Add Dummy CUCM class for permissions use for now.
use App\Cucmclass;
use App\PhoneMACD;
use Illuminate\Http\Request;
use App\Events\Create_Line_Event;
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

        // Clear the token, we don't want to save that data.
        unset($phone['token']);

        $macd = PhoneMACD::create(['type' => 'MACD', 'form_data' => $phone, 'created_by' => $user->username]);

        $tasks = [];

        $data['phone'] = $phone;

        // Update AD User IP Phone Field
        if (isset($phone['username']) && $phone['username'] && (isset($phone['dn']) && $phone['dn'])) {
            $task = PhoneMACD::create(['type' => 'Update User AD IP Phone Field', 'parent' => $macd->id, 'status' => 'job recieved']);
            $tasks[] = $task;
            $data['taskid'] = $task->id;

            // Testing of Events Controller
            event(new Create_AD_IPPhone_Event($data));
        }

        // Build new line first and then chain to add the phone if a new line is required.
        if (isset($phone['usenumber']) && $phone['usenumber'] == 'new') {
            $task = PhoneMACD::create(['type' => 'Add Line', 'parent' => $macd->id, 'status' => 'job recieved']);
            $tasks[] = $task;
            $data['taskid'] = $task->id;

            // Testing of Events Controller
            event(new Create_Line_Event($data));
        } else {
            // Build Phone
            if (isset($phone['name']) && $phone['name']) {
                $task = PhoneMACD::create(['type' => 'Add Phone', 'parent' => $macd->id, 'status' => 'job recieved']);
                $tasks[] = $task;
                $data['taskid'] = $task->id;

                // Testing of Events Controller
                event(new Create_Phone_Event($data));
            }
        }

        // Build Voicemail Box
        if (isset($phone['voicemail'])) {
            if ($phone['voicemail'] == 'true') {
                if (isset($phone['template']) && $phone['template']) {
                    if (isset($phone['username']) && $phone['username']) {
                        $task = PhoneMACD::create(['type' => 'Create Mailbox from LDAP User', 'parent' => $macd->id, 'status' => 'job recieved']);
                        $tasks[] = $task;
                        $data['taskid'] = $task->id;

                        // Testing of Events Controller
                        event(new Create_UnityConnection_LDAP_Import_Mailbox_Event($data));
                    } else {
                        // If no username build user as a new user without Unified Messaging
                        $task = PhoneMACD::create(['type' => 'Create Mailbox with no UserID', 'parent' => $macd->id, 'status' => 'job recieved']);
                        $tasks[] = $task;
                        // Create the User Alias for the mailbox.
                        $data['phone']['username'] = $data['phone']['firstname'].' '.$data['phone']['lastname'].' '.$data['phone']['dn'];

                        $data['taskid'] = $task->id;

                        // Testing of Events Controller
                        event(new Create_UnityConnection_Mailbox_Event($data));
                    }
                }
            }
        }

        $result = ['macd'     => $macd,
                   'tasks'    => $tasks,
                    ];

        $response = [
                    'status_code'          => 200,
                    'success'              => true,
                    'message'              => '',
                    'request'              => $request->all(),
                    'result'               => $result,
                    ];

        return response()->json($response);
    }

    public function list_macd_jobs_for_week(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        // Check user permissions
        if (! $user->can('read', PhoneMACD::class)) {
            if (! $user->can('read', Cucmclass::class)) {
                abort(401, 'You are not authorized');
            }
        }

        $start = \Carbon\Carbon::now();
        $end = \Carbon\Carbon::now()->subWeek();

        // Search for DID by numberCheck if there are any matches.
        if (! PhoneMACD::whereBetween('created_at', [$end, $start])
                    ->count()) {
            abort(404, 'No MACDs Found');
        }

        // Search for numbers like search.
        $macs = PhoneMACD::whereBetween('created_at', [$end, $start])
            ->orderby('created_at', 'desc')->get();

        //return "HERE ".$did;

        $response = [
                    'status_code'          => 200,
                    'success'              => true,
                    'message'              => '',
                    'request'              => $request->all(),
                    'result'               => $macs,
                    ];

        return response()->json($response);
    }

    public function list_my_macd_jobs_for_week(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        // Check user permissions
        if (! $user->can('read', PhoneMACD::class)) {
            if (! $user->can('read', Cucmclass::class)) {
                abort(401, 'You are not authorized');
            }
        }

        $start = \Carbon\Carbon::now();
        $end = \Carbon\Carbon::now()->subWeek();

        // Search for DID by numberCheck if there are any matches.
        if (! PhoneMACD::where([['created_by', $user->username]])
                ->whereBetween('created_at', [$end, $start])
                ->count()) {
            abort(404, 'No Block found matching search: '.$number_search);
        }

        // Search for numbers like search.
        $macs = PhoneMACD::where([['created_by', $user->username]])->orderby('created_at', 'desc')
            ->whereBetween('created_at', [$end, $start])
            ->get();

        //return "HERE ".$did;

        $response = [
                    'status_code'          => 200,
                    'success'              => true,
                    'message'              => '',
                    'request'              => $request->all(),
                    'result'               => $macs,
                    ];

        return response()->json($response);
    }

    public function list_macd_and_children_by_id(Request $request, $id)
    {
        $user = JWTAuth::parseToken()->authenticate();

        // Check user permissions
        if (! $user->can('read', PhoneMACD::class)) {
            if (! $user->can('read', Cucmclass::class)) {
                abort(401, 'You are not authorized');
            }
        }

        $macd = PhoneMACD::find($id);

        // Search for DID by numberCheck if there are any matches.
        if (! PhoneMACD::where('parent', '=', $id)
                ->count()) {
            $tasks = [];
        } else {
            // Search for numbers like search.
            $tasks = PhoneMACD::where('parent', '=', $id)->get();
        }

        $result = ['macd'     => $macd,
                   'tasks'    => $tasks,
                    ];

        $response = [
                    'status_code'           => 200,
                    'success'               => true,
                    'message'               => '',
                    'request'               => $request->all(),
                    'result'                => $result,
                    ];

        return response()->json($response);
    }
}
