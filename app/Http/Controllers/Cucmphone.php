<?php

namespace App\Http\Controllers;

// Add Dummy CUCM class for permissions use for now.
use App\Cucmclass;
use Illuminate\Http\Request;
// Include the JWT Facades shortcut
use Tymon\JWTAuth\Facades\JWTAuth;

class Cucmphone extends Cucm
{
    public $phones;

    public function uploadPhones(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        // Check user permissions
        if (! $user->can('create', Cucmclass::class)) {
            abort(401, 'You are not authorized');
        }

        // Open CSV and Create a new DID Block for each row.
        $filename = $request->phones;
            //return $filename;
            if (! file_exists($filename) || ! is_readable($filename)) {
                return 'Something is jacked with the file';
            }

        $delimiter = ',';
        $header = null;
        $data = [];
        if (($handle = fopen($filename, 'r')) !== false) {
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== false) {
                if (! $header) {
                    $header = $row;
                } else {
                    $data[] = array_combine($header, $row);
                }
            }
            fclose($handle);
        }

        print_r($data);
    }

    public function phones_string_to_array($INPUT)
    {

        //print_r($INPUT);
        $INPUT = explode(PHP_EOL, $INPUT);
        //print_r($INPUT);

        $PHONES = [];
        foreach ($INPUT as $LINE) {
            if ($LINE == '') {
                unset($LINE);
                continue;
            }
            //$LINE = explode("\t",$LINE);
            //print_r($LINE);
            $PHONE = array_combine(
                                // And map these keys to each value extracted
                                [
                                    'firstname',    'lastname',    'username',        'name',        'device',
                                    'dn',    'language',    'defaultpass',    'voicemail',    'notes',
                                ], explode("\t", $LINE)
                            );
            $PHONES[] = $PHONE;
        }

        return $PHONES;
    }

    public function pastePhones(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        // Check user permissions
        if (! $user->can('create', Cucmclass::class)) {
            abort(401, 'You are not authorized');
        }

        $INPUT = $request->phones;

        $INPUT = explode(',', $INPUT);

        $PHONES = [];
        foreach ($INPUT as $LINE) {
            //$LINE = explode("\t",$LINE);
            //print_r($LINE);
            $PHONE = array_combine(
                                // And map these keys to each value extracted
                                [
                                    'firstname',    'lastname',    'username',       'name',        'device',
                                    'dn',    'language',    'defaultpass',    'voicemail',    'notes',
                                ], explode("\t", $LINE)
                            );
            $PHONE['site'] = $request->sitecode;
            $PHONE['extlength'] = $request->extlength;

            $PHONES[] = $PHONE;
        }

        return $PHONES;
    }

    public function getPhone(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        // Check user permissions
        if (! $user->can('read', Cucmclass::class)) {
            abort(401, 'You are not authorized');
        }

        $name = $request->name;
        $phone = '';
        try {
            $phone = $this->cucm->get_phone_by_name($name);

            if (! count($phone)) {
                throw new \Exception('Indexed results from call mangler is empty');
            }
        } catch (\Exception $e) {
            $exception = 'Callmanager blew up: '.$e->getMessage().PHP_EOL;
            //dd($e->getTrace());
        }

        if ($phone) {
            // Append Line Details to the phone.

            $phone['line_details'] = $this->cucm->get_lines_details_by_phone_name($name);
        } else {
            $phone = 'Not Found';
        }

        $response = [
                    'status_code'    => 200,
                    'success'        => true,
                    'message'        => '',
                    'response'       => $phone,
                    ];

        return response()->json($response);
    }

    public function deletePhonebyName($NAME)
    {
        $user = JWTAuth::parseToken()->authenticate();
        // Check user permissions
        if (! $user->can('delete', Cucmclass::class)) {
            abort(401, 'You are not authorized');
        }

        // Try to remove device from CUCM
        try {
            $RESULT = $this->cucm->get_phone_by_name($NAME);
            $TYPE = 'Phone';
            if (is_array($RESULT) && ! empty($RESULT)) {
                $UUID = $RESULT['uuid'];
                $RETURN['old'] = $RESULT;
                $RETURN['deleted_uuid'] = $this->cucm->delete_object_type_by_uuid($UUID, $TYPE);

                // Create log entry
                activity('cucm_provisioning_log')->causedBy($user)->withProperties(['function' => __FUNCTION__, 'return' => $RETURN])->log('delete object');

                return $RETURN;
            }
        } catch (\Exception $E) {
            //return "{$NAME} Does not exist in CUCM Database.\n";
        }
    }

    public function deletePhone(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        // Check user permissions
        if (! $user->can('delete', Cucmclass::class)) {
            abort(401, 'You are not authorized');
        }

        // Check if name is Set
        if (! isset($request->name) || ! $request->name) {
            return 'Error, no name set';
        }
        $NAME = $request->name;

        return $this->deletePhonebyName($NAME);
    }

    // CUCM Add Phone Wrapper
    public function wrap_add_phone_object($DATA, $TYPE)
    {
        $user = JWTAuth::parseToken()->authenticate();
        // Check user permissions
        if (! $user->can('create', Cucmclass::class)) {
            abort(401, 'You are not authorized');
        }

        // Get the name to reference the object.
        if (isset($DATA['name'])) {
            $OBJECT = $DATA['name'];
        } elseif (isset($DATA['pattern'])) {
            $OBJECT = $DATA['pattern'];
        } else {
            $OBJECT = $TYPE;
        }
        try {
            $REPLY = $this->cucm->add_object_type_by_assoc($DATA, $TYPE);

            $LOG = [
                    'type'       => $TYPE,
                    'object'     => $OBJECT,
                    'status'     => 'success',
                    'reply'      => $REPLY,
                    'request'    => $DATA,

                ];

            // Create log entry
            activity('cucm_provisioning_log')->causedBy($user)->withProperties(['function' => __FUNCTION__, $LOG])->log('add object');

            $this->results[$TYPE] = $LOG;

            return $REPLY;
        } catch (\Exception $E) {
            $EXCEPTION = "Exception adding object type: {$TYPE}".
                  "{$E->getMessage()}";
                  /*"Stack trace:\n".
                  "{$E->getTraceAsString()}".
                  "Data sent:\n";*/
            //$delimiter = "Stack trace:";
            //explode ($delimiter , $EXCEPTION);
            $this->results[$TYPE] = [
                                        'type'         => $TYPE,
                                        'object'       => $OBJECT,
                                        'status'       => 'error',
                                        'request'      => $DATA,
                                        'exception'    => $EXCEPTION,
                                    ];
        }
    }

    // Create New Phone
    public function createPhone(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        // Check user permissions
        if (! $user->can('create', Cucmclass::class)) {
            abort(401, 'You are not authorized');
        }

        $errors = [];

        // Check if sitecode is Set
        if (! isset($request->sitecode) || ! $request->sitecode) {
            $errors[] = 'Error, no sitecode set';
        }
        $SITE = $request->sitecode;

        // Check if device is Set
        if (! isset($request->device) || ! $request->device) {
            $errors[] = 'Error, no device set';
        }
        $DEVICE = $request->device;

        // Check if name is Set
        if (! isset($request->name) || ! $request->name) {
            $errors[] = 'Error, no name set';
        }
        $NAME = $request->name;

        // Check if firstname is Set
        if (! isset($request->firstname) || ! $request->firstname) {
            $errors[] = 'Error, no firstname set';
        }
        $FIRSTNAME = $request->firstname;

        // Check if lastname is Set
        if (! isset($request->lastname) || ! $request->lastname) {
            $errors[] = 'Error, no lastname set';
        }
        $LASTNAME = $request->lastname;

        // Check if username is Set
        if (! isset($request->username) || ! $request->username) {
            $USERNAME = 'CallManager.Unassign';
            //return 'Error, no username set';
        } else {
            $USERNAME = $request->username;
        }

        // Check if dn is Set
        if (! isset($request->dn) || ! $request->dn) {
            $errors[] = 'Error, no dn set';
        }
        $DN = $request->dn;

        // Check if extlength is Set
        if (! isset($request->extlength) || ! $request->extlength) {
            $errors[] = 'Error, no extlength set';
        }
        $EXTENSIONLENGTH = $request->extlength;

        // Check if language is Set
        if (! isset($request->language) || ! $request->language) {
            //$errors[] = 'Error, no language set';
            $LANGUAGE = 'english';
        }
        $LANGUAGE = $request->language;

        // Check if voicemail is Set
        if (! isset($request->voicemail) || ! $request->voicemail) {
            $errors[] = 'Error, no voicemail set';
        }
        $VOICEMAIL = $request->voicemail;

        // Check if notes is Set
        if (isset($request->notes) && $request->notes) {
            $NOTES = $request->notes;
        }

        if ((isset($errors)) && ! empty($errors)) {
            $result['Phone'] = [
                        'type'         => 'Phone',
                        'object'       => $request->name,
                        'status'       => 'error',
                        'request'      => $request->all,
                        'exception'    => $errors,
                    ];

            $response = [
                        'status_code'    => 200,
                        'success'        => true,
                        'message'        => '',
                        'response'       => $result,
                        ];

            return response()->json($response);
        }

        // Final user information required to provision phone:
        $result = $this->provision_cucm_phone_axl(
                                                $SITE,
                                                $DEVICE,
                                                $NAME,
                                                $FIRSTNAME,
                                                $LASTNAME,
                                                $USERNAME,
                                                $DN,
                                                $EXTENSIONLENGTH,
                                                $LANGUAGE,
                                                $VOICEMAIL
                                                );
        $response = [
            'status_code'    => 200,
            'success'        => true,
            'message'        => '',
            'response'       => $result,
            ];

        return response()->json($response);
    }

    // Build all the elements needed for the site.

    private function provision_cucm_phone_axl(
                                                $SITE,
                                                $DEVICE,
                                                $NAME,
                                                $FIRSTNAME,
                                                $LASTNAME,
                                                $USERNAME,
                                                $DN,
                                                $EXTENSIONLENGTH,
                                                $LANGUAGE,
                                                $VOICEMAIL
                                            ) {
        $NAME = strtoupper($NAME);

        $FULLNAME = implode(' ', [$FIRSTNAME, $LASTNAME]);
        $SHORTDN = substr($DN, 0 - $EXTENSIONLENGTH);
        // 30 is max, off-by-1 is 29, space-dash-space is 3, shortdn length could be 4-10
        $SHORTDESC = substr($FULLNAME, 0, 25 - strlen($SHORTDN)).' - '.$SHORTDN;
        // 50 is max, off-by-1 is 49, space-dash-space is 3, shortdn length could be 4-10
        $DESCRIPTION = substr($FULLNAME, 0, 45 - strlen($SHORTDN)).' - '.$SHORTDN;
        //$DESCRIPTION = $FULLNAME . " - " . $SHORTDN;
        $PRODUCT = 'Cisco '.$DEVICE;

        // add the SEP to the name
        if ($PRODUCT == 'Cisco IP Communicator') {
            $NAME = "{$NAME}";
        } else {
            $NAME = "SEP{$NAME}";
        }
        if (isset($USERNAME)) {
            if (! $USERNAME) {
                $USERNAME = 'CallManager.Unassign';
            }
        }

        // User selected / database provided SITE information
        // $EXTENSIONLENGTH = 4; // user input, 4 5 or 10 digit dialing shortcut
        $PROTOCOL = 'SCCP';
        $SOFTKEYTEMPLATE = 'Standard Feature - Kiewit';
        $VOICEMAILPROFILE = 'Default';
        $LINECSS = 'CSS_LINEONLY_L4_INTL';

        $PHONELINE = [
                    'pattern'                      => $DN,
                    'description'                  => $DESCRIPTION,
                    'routePartitionName'           => 'Global-All-Lines',
                    'usage'                        => '',
                    'shareLineAppearanceCssName'   => $LINECSS,
                    'alertingName'                 => substr($FULLNAME, 0, 28),
                    'asciiAlertingName'            => substr($FULLNAME, 0, 28),
                    'voiceMailProfileName'         => $VOICEMAILPROFILE,
                    'presenceGroupName'            => 'Standard Presence group',

                    // E164 Alternative Number Mask - This is currently being ignored by CUCM because of a Cisco Bug. Ver 10.5.2 - 12/8/16 TR - TAC Case Opened
                    'e164AltNum'                => [
                                                        'numMask'                     => "+1{$DN}",
                                                        'isUrgent'                    => 'true',
                                                        'addLocalRoutePartition'      => 'true',
                                                        'routePartition'              => 'Global-All-Lines',
                                                        'active'                      => 'true',
                                                        'advertiseGloballyIls'        => 'true',
                                                    ],

                    // Call Forward Settings
                    'callForwardAll'               => [
                                                        'forwardToVoiceMail'                 => 'false',
                                                        'callingSearchSpaceName'             => $LINECSS,
                                                        'secondaryCallingSearchSpaceName'    => "CSS_{$SITE}_DEVICE",
                                                    ],
                    'callForwardBusy'            => [
                                                        'forwardToVoiceMail'     => 'true',
                                                        'callingSearchSpaceName' => 'CSS_LINE_CFWD_LD',
                                                    ],
                    'callForwardBusyInt'        => [
                                                        'forwardToVoiceMail'     => 'true',
                                                        'callingSearchSpaceName' => 'CSS_LINE_CFWD_LD',
                                                    ],
                    'callForwardBusyInt'        => [
                                                        'forwardToVoiceMail'     => 'true',
                                                        'callingSearchSpaceName' => 'CSS_LINE_CFWD_LD',
                                                    ],
                    'callForwardNoAnswer'        => [
                                                        'forwardToVoiceMail'     => 'true',
                                                        'callingSearchSpaceName' => 'CSS_LINE_CFWD_LD',
                                                    ],
                    'callForwardNoAnswerInt'    => [
                                                        'forwardToVoiceMail'     => 'true',
                                                        'callingSearchSpaceName' => 'CSS_LINE_CFWD_LD',
                                                    ],
                    'callForwardNoCoverage'        => [
                                                        'forwardToVoiceMail'     => 'true',
                                                        'callingSearchSpaceName' => 'CSS_LINE_CFWD_LD',
                                                    ],
                    'callForwardNoCoverageInt'    => [
                                                        'forwardToVoiceMail'     => 'true',
                                                        'callingSearchSpaceName' => 'CSS_LINE_CFWD_LD',
                                                    ],
                    'callForwardOnFailure'            => [
                                                        'forwardToVoiceMail'     => 'true',
                                                        'callingSearchSpaceName' => 'CSS_LINE_CFWD_LD',
                                                    ],
                    'callForwardNotRegistered'    => [
                                                        'forwardToVoiceMail'     => 'true',
                                                        'callingSearchSpaceName' => 'CSS_LINE_CFWD_LD',
                                                    ],
                    'callForwardNotRegisteredInt' => [
                                                        'forwardToVoiceMail'     => 'true',
                                                        'callingSearchSpaceName' => 'CSS_LINE_CFWD_LD',
                                                    ],

                ];

        $PHONELINE_UPDATE = [
                    'pattern'                      => $DN,
                    'routePartitionName'           => 'Global-All-Lines',

                    // E164 Alternative Number Mask - This is currently being ignored by CUCM because of a Cisco Bug. Ver 10.5.2 - 12/8/16 TR - TAC Case Opened
                    // updateLine works so we need to add this portion with an update after the Line has been added to the system.
                    'e164AltNum'                => [
                                                        'numMask'                     => "+1{$DN}",
                                                        'isUrgent'                    => 'true',
                                                        'addLocalRoutePartition'      => 'true',
                                                        'routePartition'              => 'Global-All-Lines',
                                                        'active'                      => 'true',
                                                        'advertiseGloballyIls'        => 'true',
                                                    ],
                            ];
        // Add the line  first

        // Check protocols models that do SIP Only.
        if (preg_match('/^Cisco 88..$/', $PRODUCT)) {
            $PROTOCOL = 'SIP';
        }
            // Check protocols models that do SIP Only.
        if (preg_match('/^Cisco 78..$/', $PRODUCT)) {
            $PROTOCOL = 'SIP';
        }

        $PHONE = [
        'name'                               => $NAME,
        'description'                        => $DESCRIPTION,
        'product'                            => $PRODUCT,
        'class'                              => 'Phone',
        'protocol'                           => $PROTOCOL,
        'protocolSide'                       => 'User',
        'devicePoolName'                     => 'DP_'.$SITE,
        'callingSearchSpaceName'             => 'CSS_'.$SITE.'_DEVICE',
        'locationName'                       => 'LOC_'.$SITE,
        'commonPhoneConfigName'              => 'Standard Common Phone Profile',
        'useTrustedRelayPoint'               => 'Default',
        'softkeyTemplateName'                => $SOFTKEYTEMPLATE,
        'ownerUserName'                      => $USERNAME,
        'builtInBridgeStatus'                => 'Default',
        'packetCaptureMode'                  => 'None',
        'certificateOperation'               => '',
        'deviceMobilityMode'                 => '',
        'subscribeCallingSearchSpaceName'    => 'CSS_DEVICE_SUBSCRIBE',
        'vendorConfig'                       => [
                                    'webAccess'        => 1,
            ],
        'lines'                    => [
                                    'line' => [
                                                    'index'           => 1,
                                                    'dirn'            => [
                                                                            'pattern'            => $DN,
                                                                            'routePartitionName' => 'Global-All-Lines',
                                                                        ],
                                                    'label'              => $SHORTDESC,
                                                    'display'            => substr($FULLNAME, 0, 28),
                                                    'displayAscii'       => substr($FULLNAME, 0, 28),
                                                    'e164Mask'           => $DN,
                                                    'maxNumCalls'        => 4,
                                                    'busyTrigger'        => 2,
                                                    'associatedEndusers' => [
                                                                                'enduser' => [
                                                                                                'userId' => $USERNAME,
                                                                                            ],
                                                                            ],
                                                    // Had to change this to Gateway Preferred instead of Phone Preferred to support 7960 phone types since they do not support on 7960.
                                                    // May need to add if statement if this is needed in the future.
                                                    'recordingMediaSource' => 'Gateway Preferred',
                                                ],
                                    ],
            ];

        // Set back to SCCP after adding phone.
        $PROTOCOL = 'SCCP';

        // Handle french phones in canada
        if (strtolower($LANGUAGE) == 'french' || strtolower($LANGUAGE) == 'f') {
            //echo "french phone, setting locale...\n";
            $PHONE['userLocale'] = 'French Canada';
            $PHONE['networkLocale'] = 'Canada';
        }

        // Set the Calling Part Transformation CSS on 7940 and 7960 phones because they do not support E164 + redialing. This will replace +1 with a 9
        if (($PRODUCT == 'Cisco 7940') || ($PRODUCT == 'Cisco 7960')) {
            $PHONE['cgpnTransformationCssName'] = 'CSS_GLOBAL_GW_CALLED_XFORM';
            $PHONE['useDevicePoolCgpnTransformCss'] = 'false';
        }

        //return $PHONE;

        // Check to see if this phone already exists. If it does print out the old config and delete it.
        $REMOVED_PHONES = [];
        //echo "Checking if {$NAME} if Exists:\n";

        /* commenting removal of phone. make user manually delete phone first.
        $REMOVED = $this->deletePhonebyName($NAME);
        */

        //print_r($REMOVED);

        $RETURN = [];

        // Add Line
        $this->wrap_add_phone_object($PHONELINE, 'Line');

        // Update Line E164 Alternative Number Mask - workaround for Cisco Bug when adding Line
        $RESULT = $this->cucm->update_object_type_by_pattern_and_partition($PHONELINE_UPDATE, 'Line');

        // Add Phone
        $this->wrap_add_phone_object($PHONE, 'Phone');

        return json_decode(json_encode($this->results), true);
    }
}
