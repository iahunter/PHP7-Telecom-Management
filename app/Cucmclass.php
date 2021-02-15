<?php

namespace App;

use App\Did;
use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Cookie\FileCookieJar as FileCookieJar;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Database\Eloquent\Model;

class Cucmclass extends Model
{
    // This class does real time queries, adds, deletes and updates to the production system.

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
    public static function reset_phone($NAME)
    {
        // Construct new cucm object
        $cucm = new \Iahunter\CallmanagerAXL\Callmanager(env('CALLMANAGER_URL'),
                                                    storage_path(env('CALLMANAGER_WSDL')),
                                                    env('CALLMANAGER_USER'),
                                                    env('CALLMANAGER_PASS')
                                                    );

        try {
            $REPLY = $cucm->reset_phone($NAME);
            //return $REPLY;
            return true;
        } catch (\Exception $E) {
            //return $E->getMessage();
            return false;
        }
    }

    // CUCM Add Phone Wrapper
    public static function wrap_add_phone_object($DATA, $TYPE)
    {
        // Construct new cucm object
        $cucm = new \Iahunter\CallmanagerAXL\Callmanager(env('CALLMANAGER_URL'),
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

    public static function add_cucm_line(
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
        $FULLNAME = implode(' ', [$FIRSTNAME, $LASTNAME]);
        $SHORTDN = substr($DN, 0 - $EXTENSIONLENGTH);
        // 30 is max, off-by-1 is 29, space-dash-space is 3, shortdn length could be 4-10
        $SHORTDESC = substr($FULLNAME, 0, 25 - strlen($SHORTDN)).' - '.$SHORTDN;
        // 50 is max, off-by-1 is 49, space-dash-space is 3, shortdn length could be 4-10
        $DESCRIPTION = substr($FULLNAME, 0, 45 - strlen($SHORTDN)).' - '.$SHORTDN;
        //$DESCRIPTION = $FULLNAME . " - " . $SHORTDN;

        if (isset($USERNAME)) {
            if (! $USERNAME) {
                $USERNAME = 'CallManager.Unassign';
            }
        }

        // Construct new cucm object
        $cucm = new \Iahunter\CallmanagerAXL\Callmanager(env('CALLMANAGER_URL'),
                                                    storage_path(env('CALLMANAGER_WSDL')),
                                                    env('CALLMANAGER_USER'),
                                                    env('CALLMANAGER_PASS')
                                                    );

        $DEVICE_CSS = "CSS_{$SITE}_DEVICE";

        try {
            $sitecss = $cucm->get_object_type_by_site($SITE, 'Css');
            $sitecss = $sitecss['response'];
        } catch (\Exception $e) {
            //echo 'Callmanager blew up: '.$e->getMessage().PHP_EOL;
            //dd($e->getTrace());
        }

        if ($sitecss) {
            if (! in_array("CSS_{$SITE}_DEVICE", $sitecss)) {
                $DEVICE_CSS = "CSS_{$SITE}";
            }
        }

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

            'callForwardAll'               => [
                'forwardToVoiceMail'                 => 'false',
                'callingSearchSpaceName'             => 'CSS_LINEONLY_L3_LD',
                'secondaryCallingSearchSpaceName'    => $DEVICE_CSS,
            ],
            'callForwardBusy'            => [
                'forwardToVoiceMail'     => 'true',
                'callingSearchSpaceName' => $DEVICE_CSS,
            ],
            'callForwardBusyInt'        => [
                'forwardToVoiceMail'     => 'true',
                'callingSearchSpaceName' => $DEVICE_CSS,
            ],
            'callForwardBusyInt'        => [
                'forwardToVoiceMail'     => 'true',
                'callingSearchSpaceName' => $DEVICE_CSS,
            ],
            'callForwardNoAnswer'        => [
                'forwardToVoiceMail'     => 'true',
                'callingSearchSpaceName' => $DEVICE_CSS,
            ],
            'callForwardNoAnswerInt'    => [
                'forwardToVoiceMail'     => 'true',
                'callingSearchSpaceName' => $DEVICE_CSS,
            ],
            'callForwardNoCoverage'        => [
                'forwardToVoiceMail'     => 'true',
                'callingSearchSpaceName' => $DEVICE_CSS,
            ],
            'callForwardNoCoverageInt'    => [
                'forwardToVoiceMail'     => 'true',
                'callingSearchSpaceName' => $DEVICE_CSS,
            ],
            'callForwardOnFailure'            => [
                'forwardToVoiceMail'     => 'true',
                'callingSearchSpaceName' => $DEVICE_CSS,
            ],
            'callForwardNotRegistered'    => [
                'forwardToVoiceMail'     => 'true',
                'callingSearchSpaceName' => $DEVICE_CSS,
            ],
            'callForwardNotRegisteredInt' => [
                'forwardToVoiceMail'     => 'true',
                'callingSearchSpaceName' => $DEVICE_CSS,
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

        $TYPE = 'Line';

        try {
            $REPLY = $cucm->add_object_type_by_assoc($PHONELINE, $TYPE);

            // Reserve the DID in the database by marking inuse
            if ($REPLY && \App\Did::where('number', $DN)->count()) {
                $did = \App\Did::where('number', $DN)->first();
                if ($did->status == 'available') {
                    $did->status = 'inuse';
                    $did->assignments = '';
                    $did->system_id = 'Reserved by MACD Tool';
                    $did->save();

                    //return 'DID Was Saved';
                }
            }
        } catch (\Exception $E) {
            $EXCEPTION = "{$E->getMessage()}";
            //return $EXCEPTION;
            throw new \Exception($E->getMessage());
        }

        try {
            $REPLY = $cucm->update_object_type_by_pattern_and_partition($PHONELINE_UPDATE, $TYPE);
        } catch (\Exception $E) {
            $EXCEPTION = "{$E->getMessage()}";
            //return $EXCEPTION;
            throw new \Exception($E->getMessage());
        }

        return json_decode(json_encode($REPLY), true);
    }

    public static function updatePhoneSite($NAME, $SITE)
    {
        // Construct new cucm object
        $cucm = new \Iahunter\CallmanagerAXL\Callmanager(env('CALLMANAGER_URL'),
                                                    storage_path(env('CALLMANAGER_WSDL')),
                                                    env('CALLMANAGER_USER'),
                                                    env('CALLMANAGER_PASS')
                                                    );

        $DEVICE_CSS = "CSS_{$SITE}_DEVICE";

        try {
            $sitecss = $cucm->get_object_type_by_site($SITE, 'Css');
            $sitecss = $sitecss['response'];
        } catch (\Exception $e) {
            //echo 'Callmanager blew up: '.$e->getMessage().PHP_EOL;
            //dd($e->getTrace());
        }

        if ($sitecss) {
            if (! in_array("CSS_{$SITE}_DEVICE", $sitecss)) {
                $DEVICE_CSS = "CSS_{$SITE}";
            }
        }

        //echo 'CSS set to: '.$CSS.PHP_EOL;

        //die();

        $TYPE = 'Phone';
        $DATA = [
            'name'                               => $NAME,
            'devicePoolName'                     => 'DP_'.$SITE,
            'callingSearchSpaceName'             => $DEVICE_CSS,
            'locationName'                       => 'LOC_'.$SITE,
            'subscribeCallingSearchSpaceName'    => 'CSS_DEVICE_SUBSCRIBE',
        ];

        try {
            $result = $cucm->update_object_type_by_assoc($DATA, $TYPE);

            $REPLY = [
                'request'         => $DATA,
                'response'        => $result,
            ];
        } catch (\Exception $E) {
            $EXCEPTION = "{$E->getMessage()}";
            //return $EXCEPTION;
            throw new \Exception($E->getMessage());
        }

        return json_decode(json_encode($REPLY), true);
    }

    public static function add_cucm_phone(
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
        $MAXCALLS = 4;
        $BUSYTRIGGER = 2;
        // add the SEP to the name
        $PRODUCT = trim($PRODUCT);
        if ($PRODUCT == 'Cisco IP Communicator' || $PRODUCT == 'IP Communicator') {
            $NAME = "{$NAME}";
        } elseif ($PRODUCT == 'Cisco CTI Route Point' || $PRODUCT == 'CTI Route Point') {
            $NAME = "{$NAME}";
        } elseif ($PRODUCT == 'Cisco ATA 190' || $PRODUCT == 'Cisco ATA 187' || $PRODUCT == 'Cisco ATA 186') {
            $NAME = "ATA{$NAME}";
            $MAXCALLS = 1;
            $BUSYTRIGGER = 1;
        } else {
            $NAME = "SEP{$NAME}";
        }
        \Log::info('############ Username', ['data' => $USERNAME]);
        $USERNAME = trim($USERNAME);
        if (isset($USERNAME)) {
            if (! $USERNAME) {
                $USERNAME = 'CallManager.Unassign';
            }
        }

        // Construct new cucm object
        $cucm = new \Iahunter\CallmanagerAXL\Callmanager(env('CALLMANAGER_URL'),
                                                    storage_path(env('CALLMANAGER_WSDL')),
                                                    env('CALLMANAGER_USER'),
                                                    env('CALLMANAGER_PASS')
                                                    );

        $DEVICE_CSS = "CSS_{$SITE}_DEVICE";

        try {
            $sitecss = $cucm->get_object_type_by_site($SITE, 'Css');
            $sitecss = $sitecss['response'];
        } catch (\Exception $e) {
            //echo 'Callmanager blew up: '.$e->getMessage().PHP_EOL;
            //dd($e->getTrace());
        }

        if ($sitecss) {
            if (! in_array("CSS_{$SITE}_DEVICE", $sitecss)) {
                $DEVICE_CSS = "CSS_{$SITE}";
            }
        }

        // User selected / database provided SITE information
        // $EXTENSIONLENGTH = 4; // user input, 4 5 or 10 digit dialing shortcut
        $PROTOCOL = 'SCCP';
        $SOFTKEYTEMPLATE = 'Standard Feature - Kiewit';

        // Check protocols models that do SIP Only.
        if (preg_match('/^Cisco 99..$/', $PRODUCT)) {
            $PROTOCOL = 'SIP';
        }

        // Check protocols models that do SIP Only.
        if (preg_match('/^Cisco 88..$/', $PRODUCT)) {
            $PROTOCOL = 'SIP';
        }

        // Check protocols models that do SIP Only.
        if (preg_match('/^Cisco 78..$/', $PRODUCT)) {
            $PROTOCOL = 'SIP';
        }

        // Check protocols models that do SIP Only.
        if (preg_match('/^Cisco Spark/', $PRODUCT)) {
            $PROTOCOL = 'SIP';
        }

        // Check protocols models that do SIP Only.
        if (preg_match('/^Cisco ATA ...$/', $PRODUCT)) {
            $PROTOCOL = 'SIP';
        }

        $PHONE = [
            'name'                                => $NAME,
            'description'                         => $DESCRIPTION,
            'product'                             => $PRODUCT,
            'class'                               => 'Phone',
            'protocol'                            => $PROTOCOL,
            'protocolSide'                        => 'User',
            'devicePoolName'                      => 'DP_'.$SITE,
            'callingSearchSpaceName'              => $DEVICE_CSS,
            'locationName'                        => 'LOC_'.$SITE,
            'commonPhoneConfigName'               => 'Standard Common Phone Profile',
            'useTrustedRelayPoint'                => 'Default',
            'softkeyTemplateName'                 => $SOFTKEYTEMPLATE,
            'ownerUserName'                       => $USERNAME,
            'builtInBridgeStatus'                 => 'On',
            'packetCaptureMode'                   => 'None',
            'certificateOperation'                => '',
            'deviceMobilityMode'                  => '',
            'subscribeCallingSearchSpaceName'     => 'CSS_DEVICE_SUBSCRIBE',
            'securityProfileName'                 => "{$PRODUCT} - Standard {$PROTOCOL} Non-Secure Profile",
            'vendorConfig'                        => [
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
                    'maxNumCalls'        => $MAXCALLS,
                    'busyTrigger'        => $BUSYTRIGGER,
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

        // Reduce max calls for the 7936 phone. Update
        if ($PRODUCT == 'Cisco 7936') {
            $PHONE['lines']['line']['maxNumCalls'] = 2;
            $PHONE['lines']['line']['busyTrigger'] = 1;
        }

        // Set the Calling Part Transformation CSS on 7940 and 7960 phones because they do not support E164 + redialing. This will replace +1 with a 9
        if (($PRODUCT == 'Cisco 7940') || ($PRODUCT == 'Cisco 7960')) {
            $PHONE['cgpnTransformationCssName'] = 'CSS_GLOBAL_GW_CALLED_XFORM';
            $PHONE['useDevicePoolCgpnTransformCss'] = 'false';
        }

        // Set the Calling Part Transformation CSS on 7940 and 7960 phones because they do not support E164 + redialing. This will replace +1 with a 9
        if (($PRODUCT == 'Cisco 7940') || ($PRODUCT == 'Cisco 7960')) {
            $PHONE['cgpnTransformationCssName'] = 'CSS_GLOBAL_GW_CALLED_XFORM';
            $PHONE['useDevicePoolCgpnTransformCss'] = 'false';
        }

        // Set the Calling Part Transformation CSS on 7940 and 7960 phones because they do not support E164 + redialing. This will replace +1 with a 9
        if (($PRODUCT == 'CTI Route Point') || ($PRODUCT == 'Cisco CTI Route Point')) {
            $PRODUCT = 'CTI Route Point';
            $PHONE['product'] = 'CTI Route Point';
            $PHONE['model'] = 'CTI Route Point';
            $PHONE['class'] = 'CTI Route Point';
        }

        // Add support for Third-party SIP Devices
        if (preg_match('/^Third-party SIP Device/', $DEVICE)) {
            $PHONE['product'] = $DEVICE;
            $PHONE['protocol'] = 'SIP';
            //$PHONE['digestUser'] = $PHONE['ownerUserName']; // Found out this field is case sensitive.
            $PHONE['digestUser'] = $USERNAME;

            // Try to verify username with End User table from CUCM to make sure user exists.
            try {
                $RESPONSE = $cucm->get_user_by_username($USERNAME);
                \Log::info('getUser', [$RESPONSE]);
                if ($RESPONSE['userid']) {
                    $USERNAME = $RESPONSE['userid'];
                    $PHONE['digestUser'] = $USERNAME;
                } else {
                    // Create a local User in CUCM.
                    sleep(5);
                }
            } catch (\Exception $e) {
                //return 'Callmanager blew up: '.$e->getMessage().PHP_EOL;
                //return $e->getTrace());
                throw new \Exception($e->getMessage());
            }

            if ($DEVICE == 'Third-party SIP Device (Advanced)') {
                $PHONE['securityProfileName'] = 'Third-party SIP Device Advanced - Digest Required';
                //$securityProfileName =  "Third-party SIP Device Advanced - Digest Required";
                //$securityProfileName =  "{$PRODUCT} - Digest Required";
            }
            if ($DEVICE == 'Third-party SIP Device (Basic)') {
                $PHONE['securityProfileName'] = 'Third-party SIP Device Basic - Digest Required';
                //$securityProfileName =  "Third-party SIP Device Basic - Digest Required";
                //$securityProfileName =  "{$PRODUCT} - Digest Required";
            }
        }

        $TYPE = 'Phone';

        try {
            $REPLY = $cucm->add_object_type_by_assoc($PHONE, $TYPE);

            // Reserve the DID in the database.
            if ($REPLY) {
                if (! \App\Cucmphoneconfigs::where('name', $PHONE['name'])->count()) {
                    $phone = \App\Cucmphoneconfigs::create(['name' => $PHONE['name']]);
                }
            }
        } catch (\Exception $E) {
            $EXCEPTION = [$E->getMessage(), $PHONE];
            //return $EXCEPTION;

            throw new \Exception($E->getMessage());
        }

        return json_decode(json_encode($REPLY), true);
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
        $cucm = new \Iahunter\CallmanagerAXL\Callmanager(env('CALLMANAGER_URL'),
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
        $PRODUCT = trim($PRODUCT);
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

        $DEVICE_CSS = "CSS_{$SITE}_DEVICE";

        try {
            $sitecss = $cucm->get_object_type_by_site($SITE, 'Css');
            $sitecss = $sitecss['response'];
        } catch (\Exception $e) {
            //echo 'Callmanager blew up: '.$e->getMessage().PHP_EOL;
            //dd($e->getTrace());
        }

        if ($sitecss) {
            if (! in_array("CSS_{$SITE}_DEVICE", $sitecss)) {
                $DEVICE_CSS = "CSS_{$SITE}";
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
                'callingSearchSpaceName'             => 'CSS_LINEONLY_L3_LD',
                'secondaryCallingSearchSpaceName'    => $DEVICE_CSS,
            ],
            'callForwardBusy'            => [
                'forwardToVoiceMail'     => 'true',
                'callingSearchSpaceName' => $DEVICE_CSS,
            ],
            'callForwardBusyInt'        => [
                'forwardToVoiceMail'     => 'true',
                'callingSearchSpaceName' => $DEVICE_CSS,
            ],
            'callForwardBusyInt'        => [
                'forwardToVoiceMail'     => 'true',
                'callingSearchSpaceName' => $DEVICE_CSS,
            ],
            'callForwardNoAnswer'        => [
                'forwardToVoiceMail'     => 'true',
                'callingSearchSpaceName' => $DEVICE_CSS,
            ],
            'callForwardNoAnswerInt'    => [
                'forwardToVoiceMail'     => 'true',
                'callingSearchSpaceName' => $DEVICE_CSS,
            ],
            'callForwardNoCoverage'        => [
                'forwardToVoiceMail'     => 'true',
                'callingSearchSpaceName' => $DEVICE_CSS,
            ],
            'callForwardNoCoverageInt'    => [
                'forwardToVoiceMail'     => 'true',
                'callingSearchSpaceName' => $DEVICE_CSS,
            ],
            'callForwardOnFailure'            => [
                'forwardToVoiceMail'     => 'true',
                'callingSearchSpaceName' => $DEVICE_CSS,
            ],
            'callForwardNotRegistered'    => [
                'forwardToVoiceMail'     => 'true',
                'callingSearchSpaceName' => $DEVICE_CSS,
            ],
            'callForwardNotRegisteredInt' => [
                'forwardToVoiceMail'     => 'true',
                'callingSearchSpaceName' => $DEVICE_CSS,
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
        if (preg_match('/^Cisco 99..$/', $PRODUCT)) {
            $PROTOCOL = 'SIP';
        }

        // Check protocols models that do SIP Only.
        if (preg_match('/^Cisco 88..$/', $PRODUCT)) {
            $PROTOCOL = 'SIP';
        }

        // Check protocols models that do SIP Only.
        if (preg_match('/^Cisco 78..$/', $PRODUCT)) {
            $PROTOCOL = 'SIP';
        }

        // Check protocols models that do SIP Only.
        if (preg_match('/^Cisco Spark/', $PRODUCT)) {
            $PROTOCOL = 'SIP';
        }

        // Check protocols models that do SIP Only.
        if (preg_match('/^Cisco ATA ...$/', $PRODUCT)) {
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
            'callingSearchSpaceName'             => $DEVICE_CSS,
            'locationName'                       => 'LOC_'.$SITE,
            'commonPhoneConfigName'              => 'Standard Common Phone Profile',
            'useTrustedRelayPoint'               => 'Default',
            'softkeyTemplateName'                => $SOFTKEYTEMPLATE,
            'ownerUserName'                      => $USERNAME,
            'builtInBridgeStatus'                => 'On',
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

        // Reduce max calls for the 7936 phone. Update
        if ($PRODUCT == 'Cisco 7936') {
            $PHONE['lines']['line']['maxNumCalls'] = 2;
            $PHONE['lines']['line']['busyTrigger'] = 1;
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

    public static function get_user_by_userid($USERNAME)
    {
        // Construct new cucm object
        $cucm = new \Iahunter\CallmanagerAXL\Callmanager(env('CALLMANAGER_URL'),
                                                    storage_path(env('CALLMANAGER_WSDL')),
                                                    env('CALLMANAGER_USER'),
                                                    env('CALLMANAGER_PASS')
                                                    );

        try {
            $REPLY = $cucm->get_user_by_username($USERNAME);

            return $REPLY;
        } catch (\Exception $E) {
            return $E->getMessage();
        }
    }

    // Add End User
    public static function add_user(array $data)
    {
        $FIRSTNAME = $data['firstname'];
        $LASTNAME = $data['lastname'];
        $USERNAME = $data['username'];

        // Construct new cucm object
        $cucm = new \Iahunter\CallmanagerAXL\Callmanager(env('CALLMANAGER_URL'),
                                                    storage_path(env('CALLMANAGER_WSDL')),
                                                    env('CALLMANAGER_USER'),
                                                    env('CALLMANAGER_PASS')
                                                    );

        $USER = [
            'firstName'				      => $FIRSTNAME,
            'lastName'				       => $LASTNAME,
            'userid'				         => $USERNAME,
            'presenceGroupName'		=> ['_'	=> 'Standard Presence group'],
            'associatedGroups' 		=> ['userGroup'	=> [
                [
                    'name' => 'Standard CTI Enabled',

                ],
                [
                    'name' => 'Standard CCM End Users',
                ],
            ],
            ],
            'enableCti'				=> true,
            //"digestCredentials" => "Summer2019",

        ];

        if (isset($data['digestCredentials']) && $data['digestCredentials']) {
            $USER['digestCredentials'] = $data['digestCredentials'];
        } elseif (env('CALLMANAGER_SIP_DIGEST')) {
            $USER['digestCredentials'] = env('CALLMANAGER_SIP_DIGEST');
        }

        if (isset($data['dn']) && $data['dn']) {
            $USER['telephoneNumber'] = $data['dn'];
        }

        //return $USER;
        try {
            $REPLY = $cucm->add_user($USER);

            return $REPLY;
        } catch (\Exception $E) {
            return $E->getMessage();
        }
    }
}
