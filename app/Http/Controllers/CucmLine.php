<?php

namespace App\Http\Controllers;

// Add Dummy CUCM class for permissions use for now.
use App\Cucmclass;
use App\PhoneMACD;
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
		
		if ($request->pattern == $request->cfa_destination) {
            abort(401, 'Error! You cannot set the forwarding number the same as the line number');
        }

        if (! isset($request->cfa_destination) || ! $request->cfa_destination) {
            //abort(401, 'No CFA Destination');
            $CFA_DESTINATION = '';
        } else {
            $CFA_DESTINATION = $request->cfa_destination;
			
			$regex = "/^9..........$/";
			
			if (preg_match($regex, $CFA_DESTINATION)) {
				$CFA_DESTINATION = substr($CFA_DESTINATION, 1); 
                $CFA_DESTINATION = "+1{$CFA_DESTINATION}";
            }
			
			$regex = "/^91........$/";
			
			if (preg_match($regex, $CFA_DESTINATION)) {
				//$CFA_DESTINATION = substr($CFA_DESTINATION, 2); 
                $CFA_DESTINATION = "+1{$CFA_DESTINATION}";
            }
			
			$regex = "/^91(.*)/";
			
			if (preg_match($regex, $CFA_DESTINATION)) {
				$CFA_DESTINATION = substr($CFA_DESTINATION, 2); 
                $CFA_DESTINATION = "+1{$CFA_DESTINATION}";
            }
			
			$regex = "/^1(.*)/";
			
			if (preg_match($regex, $CFA_DESTINATION)) {
				$CFA_DESTINATION = substr($CFA_DESTINATION, 1); 
                $CFA_DESTINATION = "+1{$CFA_DESTINATION}";
            }
			
			$regex = "/^\+1(.*)/";
            if (! preg_match($regex, $CFA_DESTINATION)) {
                $CFA_DESTINATION = "+1{$CFA_DESTINATION}";
            }
            //$CFA_DESTINATION = "+1{$CFA_DESTINATION}";
			
			if ($request->pattern == substr($CFA_DESTINATION, 2)) {
				abort(401, 'Error! You cannot set the forwarding number the same as the line number');
			}
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

    public function updateLine(Request $request)
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

        $TYPE = 'Line';

        $line = $request->line;

        //return $line;

        if (! isset($line['pattern']) || ! $line['pattern']) {
            abort(401, 'No Pattern');
        } else {
            $DN = $line['pattern'];
            $PHONELINE_UPDATE['pattern'] = $line['pattern'];
        }

        if (! isset($line['routePartitionName']) || ! $line['routePartitionName']) {
            abort(401, 'No Route Partition');
        } else {
            $PARTITION = $line['routePartitionName'];
            $PHONELINE_UPDATE['routePartitionName'] = $line['routePartitionName'];
        }
        if (isset($line['description']) && $line['description']) {
            $PHONELINE_UPDATE['description'] = $line['description'];
        }
        if (isset($line['e164AltNum']) && $line['e164AltNum']) {
            $PHONELINE_UPDATE['e164AltNum'] = $line['e164AltNum'];
        }
        if (isset($line['shareLineAppearanceCssName']) && $line['shareLineAppearanceCssName']) {
            $PHONELINE_UPDATE['shareLineAppearanceCssName'] = $line['shareLineAppearanceCssName'];
        }
        if (isset($line['callForwardAll']) && $line['callForwardAll']) {
            $PHONELINE_UPDATE['callForwardAll'] = $line['callForwardAll'];
        }
        if (isset($line['callForwardBusy']) && $line['callForwardBusy']) {
            $PHONELINE_UPDATE['callForwardBusy'] = $line['callForwardBusy'];
        }
        if (isset($line['callForwardBusyInt']) && $line['callForwardBusyInt']) {
            $PHONELINE_UPDATE['callForwardBusyInt'] = $line['callForwardBusyInt'];
        }
        if (isset($line['callForwardNoAnswer']) && $line['callForwardNoAnswer']) {
            $PHONELINE_UPDATE['callForwardNoAnswer'] = $line['callForwardNoAnswer'];
        }
        if (isset($line['callForwardNoAnswerInt']) && $line['callForwardNoAnswerInt']) {
            $PHONELINE_UPDATE['callForwardNoAnswerInt'] = $line['callForwardNoAnswerInt'];
        }
        if (isset($line['callForwardNoCoverage']) && $line['callForwardNoCoverage']) {
            $PHONELINE_UPDATE['callForwardNoCoverage'] = $line['callForwardNoCoverage'];
        }
        if (isset($line['callForwardNoCoverageInt']) && $line['callForwardNoCoverageInt']) {
            $PHONELINE_UPDATE['callForwardNoCoverageInt'] = $line['callForwardNoCoverageInt'];
        }
        if (isset($line['callForwardOnFailure']) && $line['callForwardOnFailure']) {
            $PHONELINE_UPDATE['callForwardOnFailure'] = $line['callForwardOnFailure'];
        }
        if (isset($line['callForwardNotRegistered']) && $line['callForwardNotRegistered']) {
            $PHONELINE_UPDATE['callForwardNotRegistered'] = $line['callForwardNotRegistered'];
        }
        if (isset($line['callForwardNotRegisteredInt']) && $line['callForwardNotRegisteredInt']) {
            $PHONELINE_UPDATE['callForwardNotRegisteredInt'] = $line['callForwardNotRegisteredInt'];
        }

        try {
            $line = $this->cucm->get_object_type_by_pattern_and_partition($DN, $PARTITION, $TYPE);

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

        try {
            $REPLY = $this->cucm->update_object_type_by_pattern_and_partition($PHONELINE_UPDATE, $TYPE);
            $LOG = [
                        'type'       => $TYPE,
                        'object'     => $DN,
                        'status'     => 'success',
                        'reply'      => $REPLY,
                        'request'    => $PHONELINE_UPDATE,

                    ];
        } catch (\Exception $e) {
            $EXCEPTION = 'Callmanager blew up: '.$e->getMessage().PHP_EOL;
            $LOG = [
                                        'type'         => $TYPE,
                                        'object'       => $DN,
                                        'status'       => 'error',
                                        'request'      => $PHONELINE_UPDATE,
                                        'exception'    => $EXCEPTION,
                                    ];
        }

        // Create log entry
        activity('cucm_provisioning_log')->causedBy($user)->withProperties(['function' => __FUNCTION__, 'return' => $line])->log('get object');
        activity('cucm_provisioning_log')->causedBy($user)->withProperties(['function' => __FUNCTION__, 'update' => $PHONELINE_UPDATE, 'return' => $LOG])->log('update object');

        $response = [
                    'status_code'    => 200,
                    'success'        => true,
                    'message'        => '',
                    'response'       => $REPLY,
                    ];

        return response()->json($LOG);
    }

    public function delete_line_by_uuid(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        // Check user permissions
        if (! $user->can('delete', Cucmclass::class)) {
            if (! $user->can('delete', PhoneMACD::class)) {
                abort(401, 'You are not authorized');
            }
        }

        $UUID = $request->uuid;
        $TYPE = 'Line';

        $REPLY = [];

        try {
            $LINE = $this->cucm->get_object_type_by_uuid($UUID, $TYPE);
            $REPLY['old'] = $LINE;

            // If Call Forwarding is active we don't want MACD folks to be able to delete. You need to have all permissions in order to do that.
            if ($LINE['callForwardAll']['destination'] != '') {
                if (! $user->can('delete', Cucmclass::class)) {
                    abort(401, 'Call Forward settings are enabled. You do not have permissions to delete this Line.');
                }
            }
        } catch (\Exception $e) {
            return 'Callmanager blew up: '.$e->getMessage().PHP_EOL;
        }

        try {
            $DELETE = $this->cucm->delete_object_type_by_uuid($UUID, $TYPE);
            $REPLY['deleted'] = $DELETE;
        } catch (\Exception $e) {
            return 'Callmanager blew up: '.$e->getMessage().PHP_EOL;
        }

        activity('cucm_provisioning_log')->causedBy($user)->withProperties(['function' => __FUNCTION__, 'delete' => $LINE])->log('delete line');

        $response = [
                    'status_code'    => 200,
                    'success'        => true,
                    'message'        => '',
                    'response'       => $REPLY,
                    ];

        return response()->json($response);
    }
}
