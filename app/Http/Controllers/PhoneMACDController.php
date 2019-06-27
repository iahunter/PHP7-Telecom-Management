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
use App\Events\Update_IDM_PhoneNumber_Event;
use App\Events\Create_UnityConnection_Mailbox_Event;
use App\Events\Create_UnityConnection_LDAP_Import_Mailbox_Event;

class PhoneMACDController extends Controller
{
    public function __construct()
    {
        // Only authenticated users can make these calls
        $this->middleware('jwt.auth');

        // Construct new cucm object
        $this->cucm = new \Iahunter\CallmanagerAXL\Callmanager(env('CALLMANAGER_URL'),
                                                    storage_path(env('CALLMANAGER_WSDL')),
                                                    env('CALLMANAGER_USER'),
                                                    env('CALLMANAGER_PASS')
                                                    );
    }

    public function queueMACD($user, $phone)
    {
        \Log::info('createPhoneMACD_Phone', ['data' => $phone]);

        if (is_array($phone)) {
            \Log::info('createPhoneMACD_Phone', ['data' => 'Phone is an array']);
        }

        // Clear the token, we don't want to save that data.
        unset($phone['token']);

        if (! isset($phone['username'])) {
            $phone['username'] = '';
        }

        if (isset($phone['phoneplan_id'])) {
            $phoneplan_id = $phone['phoneplan_id'];
        } else {
            $phoneplan_id = null;
        }

        $macd = PhoneMACD::create(['phoneplan_id' => $phoneplan_id, 'type' => 'MACD', 'form_data' => $phone, 'created_by' => $user->username]);

        $tasks = [];

        $data['phone'] = $phone;

		// Check to see if addisable is true. 
		if(isset($phone['addisable'])){
			if($phone['addisable']){
				$adupdate = false; 
			}
			else{
				$adupdate = true; 
			}
		}else{
			$adupdate = true; 
		}
		
		if($adupdate){
			// Update AD User IP Phone Field
			if (isset($phone['username']) && $phone['username']) {
				if (isset($phone['dn']) && $phone['dn']) {
					$task = PhoneMACD::create([
												'type'   => 'Update User AD IP Phone Field',
												'parent' => $macd->id,
												'status' => 'job received',
											]);
					$tasks[] = $task;
					$data['taskid'] = $task->id;

					// Testing of Events Controller
					try {
						event(new Create_AD_IPPhone_Event($data));

						// If IDM is set to true then create an event to update Telephone for user in SAP IDM.
						if (env('IDM')) {
							$task = PhoneMACD::create([
														'type'   => 'Update User IDM Telephone Number',
														'parent' => $macd->id,
														'status' => 'job received',
													]);
							$tasks[] = $task;
							$data['taskid'] = $task->id;
							event(new Update_IDM_PhoneNumber_Event($data));
						}
					} catch (\Exception $e) {
					}
				}
			}
		}


        // Build new line first and then chain to add the phone if a new line is required.
        if (isset($phone['usenumber']) && $phone['usenumber'] == 'new') {
            //\Log::info('createPhoneListener', ['data' => "New Line... Build Line Job"]);
            $task = PhoneMACD::create(['type' => 'Add Line', 'parent' => $macd->id, 'status' => 'job received']);
            $tasks[] = $task;
            $data['taskid'] = $task->id;

            // Testing of Events Controller
            event(new Create_Line_Event($data));

        // Build new line first and then chain to add the phone if a new line is required.
        } elseif (isset($phone['usenumber']) && $phone['usenumber'] == 'existing') {
            $result = $this->cucm->get_route_plan_by_name($phone['dn']);

            //\Log::info('Line Found!!!!', ['data' => $result]);

            //$task = PhoneMACD::create(['type' => 'Add Line', 'parent' => $macd->id, 'status' => 'job received']);

            // If its not built in the system go ahead and try to build it.
            if (! $result) {
                //\Log::info('No Line Found!!!', ['data' => "No Result... Add Line"]);
                $task = PhoneMACD::create(['type' => 'Add Line', 'parent' => $macd->id, 'status' => 'job received']);
                $tasks[] = $task;
                $data['taskid'] = $task->id;

                // Testing of Events Controller
                event(new Create_Line_Event($data));
            } else {
                // Build Phone
                if (isset($phone['name']) && $phone['name']) {
                    //\Log::info('createPhoneListener', ['data' => "Create the Phone"]);
                    $task = PhoneMACD::create(['type' => 'Add Phone', 'parent' => $macd->id, 'status' => 'job received']);
                    $tasks[] = $task;
                    $data['taskid'] = $task->id;

                    // Testing of Events Controller
                    event(new Create_Phone_Event($data));
                }
            }
        } else {
            // Build Phone
            if (isset($phone['name']) && $phone['name']) {
                //\Log::info('createPhoneListener', ['data' => "Create the Phone"]);
                $task = PhoneMACD::create(['type' => 'Add Phone', 'parent' => $macd->id, 'status' => 'job received']);
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
                        $task = PhoneMACD::create(['type' => 'Create Mailbox from LDAP User', 'parent' => $macd->id, 'status' => 'job received']);
                        $tasks[] = $task;
                        $data['taskid'] = $task->id;

                        // Testing of Events Controller
                        event(new Create_UnityConnection_LDAP_Import_Mailbox_Event($data));
                    } else {
                        // If no username build user as a new user without Unified Messaging
                        $task = PhoneMACD::create(['type' => 'Create Mailbox with no UserID', 'parent' => $macd->id, 'status' => 'job received']);
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

        return $result;
    }

    public function createPhoneMACD_Phone(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        // Check user permissions
        if (! $user->can('create', PhoneMACD::class)) {
            if (! $user->can('create', Cucmclass::class)) {
                abort(401, 'You are not authorized');
            }
        }

        $macd = $request->all();

        $result = $this->queueMACD($user, $macd);

        $response = [
                    'status_code'          => 200,
                    'success'              => true,
                    'message'              => '',
                    'request'              => $request->all(),
                    'result'               => $result,
                    ];

        return response()->json($response);
    }

    public function createPhoneMacdBatch(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        // Check user permissions
        if (! $user->can('create', PhoneMACD::class)) {
            if (! $user->can('create', Cucmclass::class)) {
                abort(401, 'You are not authorized');
            }
        }

        // Expect an array only.
        $macds = $request->macds;

        if (! is_array($macds)) {
            throw new \Exception('Expecting an array');
        }

        /*
            $result = ['macd'     => $macd,
                       'tasks'    => $tasks,
                        ];
        */
        $result = [];
        foreach ($macds as $macd) {
            $result[] = $this->queueMACD($user, $macd);
        }

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

        // Try to get status of all the children and set the job status of the worst status.
        foreach ($macs as $key => $mac) {
            $mac['status'] = PhoneMACD::get_parent_status($mac['id']);
            $macs[$key] = $mac;
        }

        $response = [
                    'status_code'          => 200,
                    'success'              => true,
                    'message'              => '',
                    'request'              => $request->all(),
                    'result'               => $macs,
                    ];

        return response()->json($response);
    }

    public function list_macd_parents_for_week(Request $request)
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
        if (! PhoneMACD::where('type', 'MACD')
                ->whereBetween('created_at', [$end, $start])
                ->count()) {
            abort(404, 'No MACD Logs Found');
        }

        // Search for numbers like search.
        $macs = PhoneMACD::where('type', 'MACD')
            ->whereBetween('created_at', [$end, $start])
            ->orderby('created_at', 'desc')
            ->get();

        // Try to get status of all the children and set the job status of the worst status.
        foreach ($macs as $key => $mac) {
            $mac['status'] = PhoneMACD::get_parent_status($mac['id']);
            $macs[$key] = $mac;
        }

        $response = [
                    'status_code'          => 200,
                    'success'              => true,
                    'message'              => '',
                    'request'              => $request->all(),
                    'result'               => $macs,
                    ];

        return response()->json($response);
    }

    public function list_macd_parents(Request $request)
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
        if (! PhoneMACD::where('type', 'MACD')
                ->limit(1000)
                ->count()) {
            abort(404, 'No MACD Logs Found');
        }

        // Search for numbers like search.
        $macs = PhoneMACD::where('type', 'MACD')
            ->limit(1000)
            ->orderby('created_at', 'desc')
            ->get();

        // Try to get status of all the children and set the job status of the worst status.
        foreach ($macs as $key => $mac) {
            $mac['status'] = PhoneMACD::get_parent_status($mac['id']);
            $macs[$key] = $mac;
        }

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
            abort(404, 'No MACD Logs Found');
        }

        // Search for numbers like search.
        $macs = PhoneMACD::where([['created_by', $user->username]])->orderby('created_at', 'desc')
            ->whereBetween('created_at', [$end, $start])
            ->get();

        // Try to get status of all the children and set the job status of the worst status.
        foreach ($macs as $key => $mac) {
            $mac['status'] = PhoneMACD::get_parent_status($mac['id']);
            $macs[$key] = $mac;
        }

        $response = [
                    'status_code'          => 200,
                    'success'              => true,
                    'message'              => '',
                    'request'              => $request->all(),
                    'result'               => $macs,
                    ];

        return response()->json($response);
    }

    public function list_my_macd_parents_for_week(Request $request)
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
                ->where('type', 'MACD')
                ->whereBetween('created_at', [$end, $start])
                ->count()) {
            abort(404, 'No MACD Logs Found');
        }

        // Search for numbers like search.
        $macs = PhoneMACD::where([['created_by', $user->username]])
            ->where('type', 'MACD')
            ->whereBetween('created_at', [$end, $start])
            ->orderby('created_at', 'desc')
            ->get();

        // Try to get status of all the children and set the job status of the worst status.
        foreach ($macs as $key => $mac) {
            $mac['status'] = PhoneMACD::get_parent_status($mac['id']);
            $macs[$key] = $mac;
        }

        $response = [
                    'status_code'          => 200,
                    'success'              => true,
                    'message'              => '',
                    'request'              => $request->all(),
                    'result'               => $macs,
                    ];

        return response()->json($response);
    }

    public function list_macd_parents_by_phoneplan_id(Request $request, $id)
    {
        $user = JWTAuth::parseToken()->authenticate();

        // Check user permissions
        if (! $user->can('read', PhoneMACD::class)) {
            if (! $user->can('read', Cucmclass::class)) {
                abort(401, 'You are not authorized');
            }
        }

        // Search for DID by numberCheck if there are any matches.
        if (! PhoneMACD::where([['phoneplan_id', $id]])
                ->where('type', 'MACD')
                ->count()) {

            // Changing response for better UI display.
            $response = [
                    'status_code'          => 200,
                    'success'              => true,
                    'message'              => '',
                    'request'              => $request->all(),
                    'result'               => [],
                    ];

            return response()->json($response);
        }

        // Search for numbers like search.
        $macs = PhoneMACD::where([['phoneplan_id', $id]])
            ->where('type', 'MACD')
            //->orderby('created_at', 'desc')
            ->get();

        // Try to get status of all the children and set the job status of the worst status.
        foreach ($macs as $key => $mac) {
            $mac['status'] = PhoneMACD::get_parent_status($mac['id']);
            $macs[$key] = $mac;
        }

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

        // Get status of MACD
        $macd->status = PhoneMACD::get_parent_status($id);

        //return PhoneMACD::get_parent_status($id);
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

    public function deletePhoneMACD(Request $request, $id)
    {
        $user = JWTAuth::parseToken()->authenticate();

        // Check user permissions
        if (! $user->can('read', Cucmclass::class)) {
            abort(401, 'You are not authorized');
        }

        $macd = PhoneMACD::find($id);

        $request->merge(['deleted_by' => $user->username]);

        $macd->fill($request->all());
        $macd->save();

        // Find the block in the database by id
        $macd->delete();                                                            // Delete the did block.
        $response = [
                    'status_code'    => 200,
                    'success'        => true,
                    'message'        => 'MACD '.$id.' successfully deleted',
                    'deleted_at'     => $macd->deleted_at, ];

        return response()->json($response);
    }
}
