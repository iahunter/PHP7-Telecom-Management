<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Cookie\FileCookieJar as FileCookieJar;

class Cucmclass extends Model
{
    // Added dummy class for permissions use for now.
    protected $fillable = ['uuid'];

    public function getKey()
    {
        if (! $this->uuid) {
            throw new \Exception('CUCM skeleton model has no UUID defined');
        }
        // make sure everybody agrees that we do indeed exist
        $this->exists = true;
        // ALWAYS use the LOWER CASE form of the ID if it is TEXT
        $this->uuid = strtolower($this->uuid);
        // return the lower case UUID as our unique identifier
        return $this->uuid;
    }
	
	
	public static $results = [];
	
	// CUCM Add Phone Wrapper
    public static function wrap_add_phone_object($DATA, $TYPE)
    {
		// Construct new cucm object
        $cucm = new \CallmanagerAXL\Callmanager(env('CALLMANAGER_URL'),
                                                    storage_path(env('CALLMANAGER_WSDL')),
                                                    env('CALLMANAGER_USER'),
                                                    env('CALLMANAGER_PASS')
                                                    );
		
        // Get the name to reference the object.
        if (isset($DATA['name'])) {
            $OBJECT = $DATA['name'];
        } elseif (isset($DATA['pattern'])) {
            $OBJECT = $DATA['pattern'];
        } else {
            $OBJECT = $TYPE;
        }
        try {
            $REPLY = $cucm->add_object_type_by_assoc($DATA, $TYPE);

            $LOG = [
                    'type'       => $TYPE,
                    'object'     => $OBJECT,
                    'status'     => 'success',
                    'reply'      => $REPLY,
                    'request'    => $DATA,

                ];

            // Create log entry
            

            static::$results[$TYPE] = $LOG;

            return $REPLY;
        } catch (\Exception $E) {
            $EXCEPTION = "Exception adding object type: {$TYPE}".
                  "{$E->getMessage()}";

            static::$results[$TYPE] = [
                                        'type'         => $TYPE,
                                        'object'       => $OBJECT,
                                        'status'       => 'error',
                                        'request'      => $DATA,
                                        'exception'    => $EXCEPTION,
                                    ];
        }
    }
	
	public static function provision_cucm_phone_axl(
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
        
		// Construct new cucm object
        $cucm = new \CallmanagerAXL\Callmanager(env('CALLMANAGER_URL'),
                                                    storage_path(env('CALLMANAGER_WSDL')),
                                                    env('CALLMANAGER_USER'),
                                                    env('CALLMANAGER_PASS')
                                                    );
		
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
                    /*
                    'callForwardAll'               => [
                                                        'forwardToVoiceMail'                 => 'false',
                                                        'callingSearchSpaceName'             => $LINECSS,
                                                        'secondaryCallingSearchSpaceName'    => "CSS_{$SITE}_DEVICE",
                                                    ],
                    */

                    'callForwardAll'               => [
                                                        'forwardToVoiceMail'        => 'false',
                                                        'callingSearchSpaceName'    => 'CSS_LINE_CFWD_LD',
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

        // Check to make sure the site has the new Css built or failback to the old one.

        try {
            $sitecss = $cucm->get_object_type_by_site($SITE, 'Css');
            $sitecss = $sitecss['response'];
        } catch (\Exception $e) {
            //echo 'Callmanager blew up: '.$e->getMessage().PHP_EOL;
            //dd($e->getTrace());
        }

        if ($sitecss) {
            if (! in_array("CSS_{$SITE}_DEVICE", $sitecss)) {
                $PHONE['callingSearchSpaceName'] = "CSS_{$SITE}";
            }
        }

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
        self::wrap_add_phone_object($PHONELINE, 'Line');

        // Update Line E164 Alternative Number Mask - workaround for Cisco Bug when adding Line
        $RESULT = $cucm->update_object_type_by_pattern_and_partition($PHONELINE_UPDATE, 'Line');

        // Add Phone
        self::wrap_add_phone_object($PHONE, 'Phone');

        return json_decode(json_encode(static::$results), true);
    }
	
}
