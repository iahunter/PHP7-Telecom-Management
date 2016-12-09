<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// Include the JWT Facades shortcut
use Tymon\JWTAuth\Facades\JWTAuth;

class Cucmphone extends Cucm
{
    public function getPhone(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        $name = $request->name;

        try {
            $phone = $this->cucm->get_phone_by_name($name);

            if (! count($phone)) {
                throw new \Exception('Indexed results from call mangler is empty');
            }
        } catch (\Exception $e) {
            echo 'Callmanager blew up: '.$e->getMessage().PHP_EOL;
            dd($e->getTrace());
        }
		
		// Append Line Details to the phone. 
		$phone['line_details'] = $this->cucm->get_lines_details_by_phone_name($name);

        $response = [
                    'status_code'    => 200,
                    'success'        => true,
                    'message'        => '',
                    'response'       => $phone,
                    ];

        return response()->json($response);
    }

    public function deletePhone(Request $request)
    {

        // Check if name is Set
        if (! isset($request->name) || ! $request->name) {
            return 'Error, no name set';
        }
        $NAME = $request->name;

        // Try to remove device from CUCM
        try {
            $RESULT = $this->cucm->get_phone_by_name($NAME);
            $TYPE = 'Phone';
            if (is_array($RESULT) && ! empty($RESULT)) {
                $UUID = $RESULT['uuid'];
                $RETURN['old'] = $RESULT;
                $RETURN['deleted_uuid'] = $this->cucm->delete_object_type_by_uuid($UUID, $TYPE);

                return $RETURN;
            }
        } catch (\Exception $E) {
            return "{$NAME} Does not exist in CUCM Database.\n";
        }
    }

    // Create New Phone
    public function createPhone(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        // Check if sitecode is Set
        if (! isset($request->sitecode) || ! $request->sitecode) {
            return 'Error, no sitecode set';
        }
        $SITE = $request->sitecode;

        // Check if device is Set
        if (! isset($request->device) || ! $request->device) {
            return 'Error, no device set';
        }
        $DEVICE = $request->device;

        // Check if name is Set
        if (! isset($request->name) || ! $request->name) {
            return 'Error, no name set';
        }
        $NAME = $request->name;

        // Check if firstname is Set
        if (! isset($request->firstname) || ! $request->firstname) {
            return 'Error, no firstname set';
        }
        $FIRSTNAME = $request->firstname;

        // Check if lastname is Set
        if (! isset($request->lastname) || ! $request->lastname) {
            return 'Error, no lastname set';
        }
        $LASTNAME = $request->lastname;

        // Check if username is Set
        if (! isset($request->username) || ! $request->username) {
            return 'Error, no username set';
        }
        $USERNAME = $request->username;

        // Check if dn is Set
        if (! isset($request->dn) || ! $request->dn) {
            return 'Error, no dn set';
        }
        $DN = $request->dn;

        // Check if extlength is Set
        if (! isset($request->extlength) || ! $request->extlength) {
            return 'Error, no extlength set';
        }
        $EXTENSIONLENGTH = $request->extlength;

        // Check if language is Set
        if (! isset($request->language) || ! $request->language) {
            return 'Error, no language set';
        }
        $LANGUAGE = $request->language;

        // Check if voicemail is Set
        if (! isset($request->voicemail) || ! $request->voicemail) {
            return 'Error, no voicemail set';
        }
        $VOICEMAIL = $request->voicemail;

        // Check if notes is Set
        if (isset($request->notes) && $request->notes) {
            $NOTES = $request->notes;
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
					'e164AltNum' 				=> [
														'numMask'     				=> "+1{$DN}",
														'isUrgent' 					=> "true",
														'addLocalRoutePartition' 	=> "true",
														'routePartition'			=> "Global-All-Lines",
														'active'					=> "true",
														'advertiseGloballyIls'		=> "true",
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
					'e164AltNum' 				=> [
														'numMask'     				=> "+1{$DN}",
														'isUrgent' 					=> "true",
														'addLocalRoutePartition' 	=> "true",
														'routePartition'			=> "Global-All-Lines",
														'active'					=> "true",
														'advertiseGloballyIls'		=> "true",
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
													'recordingMediaSource' => 'Phone Preferred',
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


		// Check to see if this phone already exists. If it does print out the old config and delete it.
		/*
		print "Checking if {$NAME} if Exists:\n";
		$REMOVED = $this->deletePhone($NAME);
		array_push($REMOVED_PHONES, $REMOVED);
		*/
		
		
		$RETURN = [];
		
		// Add Line
		$RETURN['line']['config'] = $PHONELINE;
		$this->wrap_add_object($PHONELINE, 'Line');
		$RETURN['line']['log'] = $this->results;
		$this->results = [];
			
		// Update Line
		$RETURN['line']['config']['e164AltNum'] = $PHONELINE;
		$RESULT = $this->cucm->update_object_type_by_pattern_and_partition($PHONELINE_UPDATE, 'Line');
		$RETURN['phone']['log']['e164AltNum'] = $RESULT;
		$this->results = [];

		// Add Phone
		$RETURN['phone']['config'] = $PHONE;
		$this->wrap_add_object($PHONE, 'Phone');
		$RETURN['phone']['log'] = $this->results;
		$this->results = [];
		
        return $RETURN;
    }
	
	
}
