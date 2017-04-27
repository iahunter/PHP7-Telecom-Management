<?php

namespace App\Http\Controllers;

// Add Dummy CUCM class for permissions use for now.
use App\Cucmclass;
use Illuminate\Http\Request;
// Include the JWT Facades shortcut
use Tymon\JWTAuth\Facades\JWTAuth;

class CucmLine extends Cucm
{
    // This probably needs moved into its own Class for Line Updates.
    public function updateLineCFWAbyPattern(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        // Check user permissions
        /*
        if (! $user->can('read', Cucmclass::class)) {
            if (! $user->can('read', self::class)) {
                abort(401, 'You are not authorized');
            }
        }
        */

        /*
        if (! isset($request->sitecode) || ! $request->sitecode) {
            abort(401, 'No Sitecode');
        } else {
            $SITE = $request->sitecode;
        }
        */

        if (isset($request->partition) && $request->partition) {
            $PARTITION = $request->partition;
        } else {
            $PARTITION = 'Global-All-Lines';
        }

        if (! isset($request->pattern) || ! $request->pattern) {
            abort(401, 'No Pattern');
        } else {
            $DN = $request->pattern;
        }

        $regex = "/^\+1(.*)/";

        if (! isset($request->cfa_destination) || ! $request->cfa_destination) {
            //abort(401, 'No CFA Destination');
            $CFA_DESTINATION = '';
        } else {
            $CFA_DESTINATION = $request->cfa_destination;
            if (! preg_match($regex, $CFA_DESTINATION)) {
                $CFA_DESTINATION = "+1{$CFA_DESTINATION}";
            }
            //$CFA_DESTINATION = "+1{$CFA_DESTINATION}";
        }

        $line = '';
        try {
            $line = $this->cucm->get_object_type_by_pattern_and_partition($DN, $PARTITION, 'Line');

            if (! count($line)) {
                throw new \Exception('Indexed results from call mangler is empty');
            }
        } catch (\Exception $e) {
            $exception = 'Callmanager blew up: '.$e->getMessage().PHP_EOL;
            //dd($e->getTrace());
        }

        if (! $line) {
            $line = 'Not Found';
            abort(404, 'No Line Found');
        } else {
            $uuid = $line['uuid'];
            $callForwardAll = $line['callForwardAll'];
        }

        if ($CFA_DESTINATION == '') {
            $callForwardAll['destination'] = '';
        } else {
            $callForwardAll['destination'] = "{$CFA_DESTINATION}";
        }

        // Check user permissions
        if (! $user->can('update', Cucmclass::class)) {
            //Testing with Permissions
            $line_instance = new Cucmclass();
            $line_instance->uuid = $line['uuid'];
            $line_instance->exists = true;
            // if the user can NOT update the line, throw an error
            if (! $user->can('update', $line_instance)) {
                // Do something to allow or deny.
                 abort(401, 'You are not authorized');
            }
        }

        $LINECSS = 'CSS_LINEONLY_L3_LD';

        $PHONELINE_UPDATE = [
                            'pattern'                          => $DN,
                            'routePartitionName'               => 'Global-All-Lines',

                            'callForwardAll'                => $callForwardAll,

                            // E164 Alternative Number Mask - This is currently being ignored by CUCM because of a Cisco Bug. Ver 10.5.2 - 12/8/16 TR - TAC Case Opened
                            // updateLine works so we need to add this portion with an update after the Line has been added to the system.

                            /*
                            'callForwardAll'                => [
                                                                'destination'                        => "+1{$CFA_DESTINATION}",
                                                                'callingSearchSpaceName'             => $LINECSS,
                                                                'secondaryCallingSearchSpaceName'    => "CSS_{$SITE}_DEVICE",
                                                            ],*/
                            ];

        // Update Line E164 Alternative Number Mask - workaround for Cisco Bug when adding Line
        $RESULT = $this->cucm->update_object_type_by_pattern_and_partition($PHONELINE_UPDATE, 'Line');
        //$RESULT = $this->cucm->update_object_type_by_uuid($uuid, 'Line');
		
		// Create log entry
		activity('cucm_provisioning_log')->causedBy($user)->withProperties(['function' => __FUNCTION__, 'return' => $line])->log('get object');
        activity('cucm_provisioning_log')->causedBy($user)->withProperties(['function' => __FUNCTION__, 'update' => $PHONELINE_UPDATE, 'return' => $RESULT])->log('update object');
		
        $response = [
                    'status_code'    => 200,
                    'success'        => true,
                    'message'        => '',
                    'response'       => $RESULT,
                    ];

        return response()->json($response);
    }

    public function getLineCFWAbyPattern(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        /*
        if (! isset($request->sitecode) || ! $request->sitecode) {
            abort(401, 'No Sitecode');
        } else {
            $SITE = $request->sitecode;
        }
        */

        if (isset($request->partition) && ! $request->partition == '') {
            $PARTITION = $request->partition;
        } else {
            $PARTITION = 'Global-All-Lines';
        }

        if (! isset($request->pattern) || ! $request->pattern) {
            abort(401, 'No Pattern');
        } else {
            $DN = $request->pattern;
        }
        //return $request;
        $line = '';

        try {
            $line = $this->cucm->get_object_type_by_pattern_and_partition($DN, $PARTITION, 'Line');

            if (! count($line)) {
                throw new \Exception('Indexed results from call mangler is empty');
            }
        } catch (\Exception $e) {
            $exception = 'Callmanager blew up: '.$e->getMessage().PHP_EOL;
            //dd($e->getTrace());
        }

        // Check user permissions
        if (! $user->can('read', Cucmclass::class)) {
            //Testing with Permissions
            $line_instance = new Cucmclass();
            $line_instance->uuid = $line['uuid'];
            $line_instance->exists = true;
            // if the user can NOT update the line, throw an error
            if (! $user->can('read', $line_instance)) {
                //print $line_instance;
                // Do something to allow or deny.
                 abort(401, 'You are not authorized');
            }
        }
        /*
        //Testing with Permissions
        $line_instance = new Cucmclass();
        $line_instance->uuid = $line['uuid'];
        $line_instance->exists = true;
        // if the user can NOT update the line, throw an error
        if(!$user->can('read', $line_instance)){
            // Do something to allow or deny.
             abort(401, 'You are not authorized');
        }*/

        if (! $line) {
            $line = 'Not Found';
            abort(404, 'No Line Found');
        } else {
            $uuid = $line['uuid'];
            $callForwardAll = $line['callForwardAll'];
        }

        $response = [
                    'status_code'    => 200,
                    'success'        => true,
                    'message'        => '',
                    'response'       => $line,
                    ];

        return response()->json($response);
    }
}
