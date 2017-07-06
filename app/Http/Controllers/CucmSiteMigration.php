<?php

namespace App\Http\Controllers;

// Add Dummy CUCM class for permissions use for now.
use App\Cucmclass;
use Illuminate\Http\Request;
// Include the JWT Facades shortcut
use Tymon\JWTAuth\Facades\JWTAuth;

//error_reporting(E_ALL);

class CucmSiteMigration extends Cucm
{
    public $SKIP_OBJECTS = [];
    public $ADD_OBJECTS = [];
    public $UPDATE_OBJECTS = [];
    public $DELETE_OBJECTS = [];

    public function migrationSiteSummary(Request $request)
    {
        /***************************************************************************************************
            We have 4 Differnet Site Designs that are supported. The Design Types are outlined below.

            Type 1	Centralized SIP and 911 Enable							SIP Trunking - E911
            Type 2	Local Gateway and migrating to 911 Enable				Local Trunking - E911
            Type 3	SIP but leveraging Local Gateway for 911				SIP Trunking - Local 911
            Type 4	Local Gateway for 911 and inbound/outbound dialing		Local Trunking - Local 911

            * Make sure to run the SiteDefaults Artisan Command so that all the dependencies are created.
            * Global-All-Lines is a partition we already had built so it is not included in SiteDefaults.
                * You will need to set this to whatever is your global Partitions for all your Lines
            * System-Voicemail is an existing partition we already had built so it is not included in SiteDefaults.
                * You will need to set this to whatever is used for your global Voicemail Ports are assigned to.
        ***************************************************************************************************/

        $user = JWTAuth::parseToken()->authenticate();
        // Check user permissions
        if (! $user->can('create', Cucmclass::class)) {
            abort(401, 'You are not authorized');
        }

        if (isset($request->type) && $request->type) {
            // Check if the user sent us the Site Design Type.
             $SITE_TYPE = $request->type;
        } elseif (isset($request->trunking) && $request->trunking && isset($request->e911) && $request->e911) {
            // Change Site type based on site design user chooses. This will determine the site type.
            if ($request->trunking == 'sip' && $request->e911 == '911enable') {
                $SITE_TYPE = 1;
            } elseif ($request->trunking == 'local' && $request->e911 == '911enable') {
                $SITE_TYPE = 2;
            } elseif ($request->trunking == 'sip' && $request->e911 == 'local') {
                $SITE_TYPE = 3;
            } elseif ($request->trunking == 'local' && $request->e911 == 'local') {
                $SITE_TYPE = 4;
            }
        }

        //return $request;
        // If the SRST IP is set, has contents, and validates as an IP address

        if (isset($request->srstip) && $request->srstip && ! filter_var($request->srstip, FILTER_VALIDATE_IP)) {
            throw new \Exception('Error: SRST invalid');
            //return 'Error: SRST invalid';
        } elseif (isset($request->srstip) && $request->srstip) {
            $SRSTIP = $request->srstip;
        } else {
            $SRSTIP = '';
        }

        // Turn the users text into an array of IP addresses
        $H323TEXT = '';
        $H323LIST = [];
        if (isset($request->h323ip) && $request->h323ip) {
            $H323TEXT = $request->h323ip;
        }
        //print $H323TEXT;
        //$H323LIST = preg_split('/\r\n|\r|\n/', $H323TEXT);
        $H323LIST = explode(',', $H323TEXT);

        // Loop through H323 IP addresses in an array and validate them as IPs
        foreach ($H323LIST as $KEY => $H323IP) {
            // If the line is blank rip it out of the list
            if (trim($H323IP) == '') {
                unset($H323LIST[$KEY]);
                continue;
            }
            // If the line has content but is NOT an ip address, abort
            if (! filter_var($H323IP, FILTER_VALIDATE_IP)) {
                throw new \Exception("Error, one of the H323 IPs provided is not valid: {$H323IP}");
                //return "Error, one of the H323 IPs provided is not valid: {$H323IP}";
            }
        }
        $H323LIST = array_values($H323LIST);

        if ($SITE_TYPE > 1) {
            // Check their NPA
            if (! isset($request->npa) || ! $request->npa) {
                throw new \Exception('Error, no npa selected');
            } else {
                $NPA = $request->npa;
            }
        } else {
            $NPA = 8675309;
        }

        // If the users site code is KHO, dump them on our subscribers
        $SITECODE = strtoupper($request->sitecode);

        // Final user information required to provision a CUCM SITE:
        $result = $this->get_cucm_site_migration_summary(
                                                $SITECODE,
                                                $SITE_TYPE,
                                                $SRSTIP,
                                                $H323LIST,
                                                $NPA
                                                );

        $response = [
            'status_code'    => 200,
            'success'        => true,
            'message'        => '',
            'response'       => $result,
            ];

        // Create log entry
        //activity('cucm_provisioning_log')->causedBy($user)->withProperties(['function' => __FUNCTION__, 'response' => $response])->log('add site');

        return response()->json($response);
    }

    private function get_cucm_site_migration_summary(
                                                $SITE,
                                                $SITE_TYPE,
                                                $SRSTIP,
                                                $H323LIST,
                                                $NPA
                                            ) {

        /***************************************************************************************************
            We have 4 Differnet Site Designs that are supported. The Design Types are outlined below.

            Type 1	Centralized SIP and 911 Enable							SIP Trunking - E911
            Type 2	Local Gateway and migrating to 911 Enable				Local Trunking - E911
            Type 3	SIP but leveraging Local Gateway for 911				SIP Trunking - Local 911
            Type 4	Local Gateway for 911 and inbound/outbound dialing		Local Trunking - Local 911

            * Make sure to run the SiteDefaults Artisan Command so that all the dependencies are created.
            * Global-All-Lines is a partition we already had built so it is not included in SiteDefaults.
                * You will need to set this to whatever is your global Partitions for all your Lines
            * System-Voicemail is an existing partition we already had built so it is not included in SiteDefaults.
                * You will need to set this to whatever is used for your global Voicemail Ports are assigned to.
        ***************************************************************************************************/

        // Get Site Summary from CUCM
        try {
            $site_array = $this->cucm->get_all_object_types_by_site($SITE);
            if (! count($site_array)) {
                throw new \Exception('Indexed results from call mangler is empty');
            }
        } catch (\Exception $e) {
            echo 'Callmanager blew up: '.$e->getMessage().PHP_EOL;
            dd($e->getTrace());
        }

        // Get Site Details from CUCM
        try {
            $site_details = $this->cucm->get_all_object_type_details_by_site($SITE);
            if (! count($site_details)) {
                throw new \Exception('Indexed results from call mangler is empty');
            }
        } catch (\Exception $e) {
            echo 'Callmanager blew up: '.$e->getMessage().PHP_EOL;
            dd($e->getTrace());
        }

        if ($SITE_TYPE == 1) {
            $TYPE = 'Srst';
            foreach ($site_array[$TYPE] as $key => $value) {
                // Delete unused objects
                $this->DELETE_OBJECTS[$TYPE][] = $site_details[$TYPE][$key];
            }
        } else {
            if ($SRSTIP) {
                // 1 - Add a SRST router
                // Calculated data structure
                $TYPE = 'Srst';
                $DATA = [
                        'name'         => "SRST_{$SITE}",
                        'ipAddress'    => $SRSTIP,
                        'port'         => 2000,
                        'SipPort'      => 5060,
                        ];

                // Check if the object already exists. If it isn't then add it.
                if (! empty($site_array[$TYPE])) {
                    if (in_array($DATA['name'], $site_array[$TYPE])) {
                        //$this->SKIP_OBJECTS[$TYPE][] = [$DATA['name'] => "Skipping... {$DATA['name']} already exists."];
                        foreach ($site_array[$TYPE] as $key => $value) {
                            if ($value == $DATA['name']) {
                                $UUID = $key;
                            }
                        }
                        $OBJECT = $site_details[$TYPE][$UUID];
                        //$this->SKIP_OBJECTS[$TYPE][] = [$DATA['name'] => $OBJECT];
                        $this->SKIP_OBJECTS[$TYPE][] = $OBJECT;
                    } else {
                        $this->ADD_OBJECTS[$TYPE][] = $DATA;
                    }
                } else {
                    $this->ADD_OBJECTS[$TYPE][] = $DATA;
                }
            }
        }

        // 2 - Add a route partition
        // Calculated variables
        $TYPE = 'RoutePartition';
        // Prepared datastructure
        $PARTITIONS = [
                        [
                        'name'                            => 'PT_'.$SITE.'_SVC',
                        'description'                     => 'Site PT, park, pickup, HG, CTI Ports, CTI-RP',
                        'useOriginatingDeviceTimeZone'    => 'true',
                        ],
                        [
                        'name'                            => 'PT_'.$SITE.'_XLATE',
                        'description'                     => 'Site Specific Translation Patterns/Speed Dials',
                        'useOriginatingDeviceTimeZone'    => 'true',
                        ],
                        /* We may no longer be using this Partition

            [
                        'name'                            => 'PT_'.$SITE,
                        'description'                     => $SITE,
                        'useOriginatingDeviceTimeZone'    => 'true',
                        ],
                        */
                    ];

        if ($SITE_TYPE >= 3) {
            // Add a 911 route partition for Site Types 3 and 4.
            $PARTITIONS[] = [
                            'name'                            => 'PT_'.$SITE.'_911',
                            'description'                     => $SITE.' 911 Calling',
                            'useOriginatingDeviceTimeZone'    => 'true',
                            ];
        }

        if (($SITE_TYPE == 2) || ($SITE_TYPE == 4)) {
            // Add a 911 route partition for Site Types 3 and 4.
            $PARTITIONS[] = [
                            'name'                            => 'PT_'.$SITE.'_GW_CALLED_XFORM',
                            'description'                     => 'Site Specific GW called party Xform',
                            'useOriginatingDeviceTimeZone'    => 'true',
                            ];
        }

        foreach ($PARTITIONS as $DATA) {
            // Check if the object already exists. If it isn't then add it.
            if (! empty($site_array[$TYPE])) {
                if (in_array($DATA['name'], $site_array[$TYPE])) {
                    //$this->SKIP_OBJECTS[$TYPE][] = [$DATA['name'] => "Skipping... {$DATA['name']} already exists."];
                    foreach ($site_array[$TYPE] as $key => $value) {
                        if ($value == $DATA['name']) {
                            $UUID = $key;
                        }
                    }
                    $OBJECT = $site_details[$TYPE][$UUID];
                    //$this->SKIP_OBJECTS[$TYPE][] = [$DATA['name'] => $OBJECT];
                    $this->SKIP_OBJECTS[$TYPE][] = $OBJECT;
                } else {
                    $this->ADD_OBJECTS[$TYPE][] = $DATA;
                }
            } else {
                $this->ADD_OBJECTS[$TYPE][] = $DATA;
            }
        }

        // Check if the object already exists. If it isn't then add it.

        if (! empty($site_array[$TYPE])) {
            foreach ($site_array[$TYPE] as $key => $value) {
                //print_r($value);
                $delete = true;
                foreach ($PARTITIONS as $DATA) {
                    if ($value == $DATA['name']) {
                        $delete = false;
                    }
                }
                if ($delete) {
                    $this->DELETE_OBJECTS[$TYPE][] = $site_details[$TYPE][$key];
                }
            }
        }

        // 3 - Add a CSS

        // Calculated variables
        $TYPE = 'Css';
        $CSS = [];

        // For Site Types 1 and 2 add CSS
        if ($SITE_TYPE <= 2) {
            $DATA = [
                'name'            => "CSS_{$SITE}_DEVICE",
                'description'     => "CSS for {$SITE} Device Assignment",
                'members'         => [
                                    'member' => [
                                                    // E911
                                                    [
                                                    'routePartitionName'   => 'PT_911Enable',
                                                    ],
                                                    [
                                                    'routePartitionName'   => "PT_{$SITE}_SVC",
                                                    ],
                                                    [
                                                    'routePartitionName'   => "PT_{$SITE}_XLATE",
                                                    ],
                                                    [
                                                    'routePartitionName'   => 'Global-All-Lines',
                                                    ],
                                                    [
                                                    'routePartitionName'   => 'System-Voicemail',
                                                    ],
                                                    [
                                                    'routePartitionName'   => 'PT_GLOBAL_SVC',
                                                    ],
                                                    [
                                                    'routePartitionName'   => 'PT_GLOBAL_XLATE',
                                                    ],
                                                    [
                                                    'routePartitionName'   => 'PT_PSTN_LOCAL_10_DIGIT',
                                                    ],
                                                    [
                                                    'routePartitionName'   => 'PT_PSTN_TOLLFREE',
                                                    ],
                                                    [
                                                    'routePartitionName'   => 'PT_PSTN_LD',
                                                    ],
                                                    [
                                                    'routePartitionName'   => 'PT_PSTN_INTL',
                                                    ],
                                                ],
                                    ],
                ];

            /* This is not working for some reason. Getting exception - Cannot insert a null into column (callingsearchspacemember.sortorder)

            if($SITE_TYPE <= 2){
                $ADD_PARTITION = ['routePartitionName'   => "PT_911Enable",];
                array_unshift($DATA['members']['member'], $ADD_PARTITION);
            }
            if($SITE_TYPE >= 3){
                $ADD_PARTITION = ['routePartitionName'   => "PT_{$SITE}_911",];
                array_unshift($DATA['members']['member'], $ADD_PARTITION);
            }
            */

            // Add the index to each member in order.
            $i = 1;
            foreach ($DATA['members']['member'] as $key => $value) {
                $value['index'] = $i++;
                $DATA['members']['member'][$key] = $value;
            }

            // Append the CSS to the $CSS Array
            $CSS[] = $DATA;
        }

        // For Site Types 3 and 4 add site specific 911 CSS and other CSSs
        if ($SITE_TYPE >= 3) {
            $DATA = [
                'name'            => "CSS_{$SITE}_DEVICE",
                'description'     => "CSS for {$SITE} Device Assignment",
                'members'         => [
                                    'member' => [
                                                    // E911
                                                    [
                                                    'routePartitionName'   => "PT_{$SITE}_911",
                                                    ],
                                                    [
                                                    'routePartitionName'   => "PT_{$SITE}_SVC",
                                                    ],
                                                    [
                                                    'routePartitionName'   => "PT_{$SITE}_XLATE",
                                                    ],
                                                    [
                                                    'routePartitionName'   => 'Global-All-Lines',
                                                    ],
                                                    [
                                                    'routePartitionName'   => 'System-Voicemail',
                                                    ],
                                                    [
                                                    'routePartitionName'   => 'PT_GLOBAL_SVC',
                                                    ],
                                                    [
                                                    'routePartitionName'   => 'PT_GLOBAL_XLATE',
                                                    ],
                                                    [
                                                    'routePartitionName'   => 'PT_PSTN_LOCAL_10_DIGIT',
                                                    ],
                                                    [
                                                    'routePartitionName'   => 'PT_PSTN_TOLLFREE',
                                                    ],
                                                    [
                                                    'routePartitionName'   => 'PT_PSTN_LD',
                                                    ],
                                                    [
                                                    'routePartitionName'   => 'PT_PSTN_INTL',
                                                    ],
                                                ],
                                    ],
                ];

            // Add the index to each member in order.
            $i = 1;
            foreach ($DATA['members']['member'] as $key => $value) {
                $value['index'] = $i++;
                $DATA['members']['member'][$key] = $value;
            }

            // Append the CSS to the $CSS Array
            $CSS[] = $DATA;
        }

        // For Site Types 2 and 4 add GW CALLED TRANFORMATIONS
        if (($SITE_TYPE == 2) || ($SITE_TYPE == 4)) {
            $DATA = [
                'name'            => "CSS_{$SITE}_GW_CALLED_XFORM",
                'description'     => 'Applied outbound on a site local trunk or gw',
                'members'         => [
                                    'member' => [
                                                    [
                                                    'routePartitionName'   => "PT_{$SITE}_GW_CALLED_XFORM",
                                                    ],
                                                    [
                                                    'routePartitionName'   => 'PT_GLOBAL_GW_CALLED_XFORM',
                                                    ],
                                                ],
                                    ],
                ];

            // Add the index to each member in order.
            $i = 1;
            foreach ($DATA['members']['member'] as $key => $value) {
                $value['index'] = $i++;
                $DATA['members']['member'][$key] = $value;
            }

            // Append the CSS to the $CSS Array
            $CSS[] = $DATA;
        }

        // For Site Types 2,3,4 add Incoming Gateway CSS
        if ($SITE_TYPE >= 2) {
            $DATA = [
                'name'            => "CSS_{$SITE}_INCOMING_GW",
                'description'     => 'Applied to incoming CSS on site gw or sip trunk',
                'members'         => [
                                    'member' => [
                                                    [
                                                    'routePartitionName'   => "PT_{$SITE}_SVC",
                                                    ],
                                                    [
                                                    'routePartitionName'   => "PT_{$SITE}_XLATE",
                                                    ],
                                                    [
                                                    'routePartitionName'   => 'Global-All-Lines',
                                                    ],
                                                    [
                                                    'routePartitionName'   => 'System-Voicemail',
                                                    ],
                                                    [
                                                    'routePartitionName'   => 'PT_GLOBAL_SVC',
                                                    ],
                                                    [
                                                    'routePartitionName'   => 'PT_GLOBAL_XLATE',
                                                    ],
                                                ],
                                    ],
                ];

            // Add the index to each member in order.
            $i = 1;
            foreach ($DATA['members']['member'] as $key => $value) {
                $value['index'] = $i++;
                $DATA['members']['member'][$key] = $value;
            }

            // Append the CSS to the $CSS Array
            $CSS[] = $DATA;
        }

        // Now add each CSS that is in the $CSS array for the site.

        foreach ($CSS as $DATA) {

            // Check if the object already exists. If it isn't then add it.
            if (! empty($site_array[$TYPE])) {
                if (in_array($DATA['name'], $site_array[$TYPE])) {
                    if ($DATA['name'] == "CSS_{$SITE}_DEVICE") {                    // Check if this is the Device CSS and if it is, check if it needs to update partition members.
                        $UUID = '';
                        foreach ($site_array[$TYPE] as $key => $value) {
                            if ($value == $DATA['name']) {
                                $UUID = $key;
                            }
                        }

                        $OBJECT = $site_details[$TYPE][$UUID];

                        $members = $this->getCssMemberNamesbyCSS($OBJECT);            // Get Member names from CSS.
                        ksort($members);                                            // Sort the members by index key.

                        //Check if this CSS needs updated if they Site type is changing.

                        $index = 0;
                        $update = false;
                        $members_array = [];

                        //////////////////////////////////////////////////////////////////////////////////////////////////////
                        // BUG!!! To update CSS Members we have to remove all of them and rebuild.
                        // PHP doesn't like member keys with same name so we need to remove all and add all back in.
                        // 	 Paying attention to the order by index. WE add our new 911 partitions to the begining of the list
                        // 	 followed by the rest in order.
                        //////////////////////////////////////////////////////////////////////////////////////////////////////

                        if ($SITE_TYPE <= 2) {
                            if (! in_array('PT_911Enable', $members)) {
                                if (in_array("PT_{$SITE}_911", $members)) {
                                    foreach ($members as $key => $value) {
                                        if ($value == "PT_{$SITE}_911") {
                                            $REMOVE = $this->remove_partition_member_to_css($DATA['name'], $value, $key);
                                            $this->UPDATE_OBJECTS[$TYPE.'removeMembers'][] = $REMOVE;
                                            unset($members[$key]);
                                        }
                                    }
                                }
                                foreach ($members as $key => $value) {
                                    // Build Array to remove each partition from the CSS. - We will need to rebuild all members after this.
                                    $REMOVE = $this->remove_partition_member_to_css($DATA['name'], $value, $key);
                                    $this->UPDATE_OBJECTS[$TYPE.'removeMembers'][] = $REMOVE;
                                }

                                // Build Array to add partition to beginning of CSS.
                                $ADD = $this->add_partition_member_to_css($DATA['name'], 'PT_911Enable', $index = 1);
                                $this->UPDATE_OBJECTS[$TYPE.'addMembers'][] = $ADD;

                                foreach ($members as $key => $value) {
                                    // Build Array to add partition to beginning of CSS.
                                    $ADD = $this->add_partition_member_to_css($DATA['name'], $value, $index = $index + 1);
                                    $this->UPDATE_OBJECTS[$TYPE.'addMembers'][] = $ADD;
                                }
                            } else {
                                $this->SKIP_OBJECTS[$TYPE][] = $OBJECT;
                            }
                        }
                        if ($SITE_TYPE >= 3) {
                            if (! in_array("PT_{$SITE}_911", $members)) {
                                if (in_array('PT_911Enable', $members)) {
                                    foreach ($members as $key => $value) {
                                        if ($value == 'PT_911Enable') {
                                            $REMOVE = $this->remove_partition_member_to_css($DATA['name'], $value, $key);
                                            $this->UPDATE_OBJECTS[$TYPE.'removeMembers'][] = $REMOVE;
                                            unset($members[$key]);
                                        }
                                    }
                                }
                                foreach ($members as $key => $value) {
                                    // Build Array to remove each partition from the CSS. - We will need to rebuild all members after this.
                                        $REMOVE = $this->remove_partition_member_to_css($DATA['name'], $value, $key);
                                    $this->UPDATE_OBJECTS[$TYPE.'removeMembers'][] = $REMOVE;
                                }

                                // Build Array to add partition to beginning of CSS.
                                $ADD = $this->add_partition_member_to_css($DATA['name'], "PT_{$SITE}_911", $index = 1);
                                $this->UPDATE_OBJECTS[$TYPE.'addMembers'][] = $ADD;

                                foreach ($members as $key => $value) {
                                    // Build Array to add partition to beginning of CSS.
                                    $ADD = $this->add_partition_member_to_css($DATA['name'], $value, $index = $index + 1);
                                    $this->UPDATE_OBJECTS[$TYPE.'addMembers'][] = $ADD;
                                }
                            } else {
                                $this->SKIP_OBJECTS[$TYPE][] = $OBJECT;
                            }
                        }
                    } else {
                        $this->SKIP_OBJECTS[$TYPE][] = $OBJECT;
                    }
                } else {
                    $this->ADD_OBJECTS[$TYPE][] = $DATA;
                }
            } else {
                $this->ADD_OBJECTS[$TYPE][] = $DATA;
            }
        }

        if ($SITE_TYPE == 1) {
            //print "Cleanup CSS";
            //print_r($site_array[$TYPE]);
            foreach ($site_array[$TYPE] as $key => $value) {
                if ($value == "CSS_{$SITE}_INCOMING_GW" || $value == "CSS_{$SITE}_GW_CALLED_XFORM") {
                    $UUID = $key;
                    $OBJECT = $site_details[$TYPE][$UUID];
                    $this->DELETE_OBJECTS[$TYPE][] = $OBJECT;                                        // Delete the CSS if type 1 and "CSS_{$SITE}_INCOMING_GW"
                }
            }
        }

        foreach ($site_array[$TYPE] as $key => $value) {
            if ($value == "CSS_{$SITE}") {
                $UUID = $key;
                $OBJECT = $site_details[$TYPE][$UUID];
                $this->DELETE_OBJECTS[$TYPE][] = $OBJECT;                                        // Delete the CSS if type 1 and "CSS_{$SITE}_INCOMING_GW"
            }
        }

        // 4 - Add a location

        // Calculated variables
        $TYPE = 'Location';
        // Prepared datastructure
        $DATA = [
                'name'                    => "LOC_{$SITE}",
                'withinAudioBandwidth'    => '0',
                'withinVideoBandwidth'    => '0',
                'withinImmersiveKbits'    => '0',
                'betweenLocations'        => [],
                ];
        // Check if the object already exists. If it isn't then add it.
        if (! empty($site_array[$TYPE])) {
            if (in_array($DATA['name'], $site_array[$TYPE])) {
                //$this->SKIP_OBJECTS[$TYPE][] = [$DATA['name'] => "Skipping... {$DATA['name']} already exists."];
                //$this->SKIP_OBJECTS[$TYPE][] = [$DATA['name'] => "Skipping... {$DATA['name']} already exists."];
                    foreach ($site_array[$TYPE] as $key => $value) {
                        if ($value == $DATA['name']) {
                            $UUID = $key;
                        }
                    }
                $OBJECT = $site_details[$TYPE][$UUID];
                $this->SKIP_OBJECTS[$TYPE][] = $OBJECT;
            } else {
                $this->ADD_OBJECTS[$TYPE][] = $DATA;
            }
        } else {
            $this->ADD_OBJECTS[$TYPE][] = $DATA;
        }

        // 5 - Add a region

        // Calculated variables
        $TYPE = 'Region';
        // Prepared datastructure
        $DATA = [
                'name'                => "R_{$SITE}",
                'relatedRegions'      => [
                                        'relatedRegion' => [
                                                                [
                                                                'regionName'                   => 'Default',
                                                                'bandwidth'                    => 'G.729',
                                                                'videoBandwidth'               => '384',
                                                                'lossyNetwork'                 => '',
                                                                'codecPreference'              => '',
                                                                'immersiveVideoBandwidth'      => '',
                                                                ],
                                                                [
                                                                'regionName'                   => 'R_711',
                                                                'bandwidth'                    => 'G.711',
                                                                'videoBandwidth'               => '384',
                                                                'lossyNetwork'                 => '',
                                                                'codecPreference'              => '',
                                                                'immersiveVideoBandwidth'      => '',
                                                                ],
                                                                [
                                                                'regionName'                   => 'R_729',
                                                                'bandwidth'                    => 'G.729',
                                                                'videoBandwidth'               => '384',
                                                                'lossyNetwork'                 => '',
                                                                'codecPreference'              => '',
                                                                'immersiveVideoBandwidth'      => '',
                                                                ],
                                                                [
                                                                'regionName'                   => "R_{$SITE}",
                                                                'bandwidth'                    => 'G.711',
                                                                'videoBandwidth'               => '384',
                                                                'lossyNetwork'                 => '',
                                                                'codecPreference'              => '',
                                                                'immersiveVideoBandwidth'      => '',
                                                                ],
                                                                [
                                                                'regionName'                   => 'R_FAX',
                                                                'bandwidth'                    => 'G.711',
                                                                'videoBandwidth'               => '384',
                                                                'lossyNetwork'                 => '',
                                                                'codecPreference'              => '',
                                                                'immersiveVideoBandwidth'      => '',
                                                                ],
                                                                [
                                                                'regionName'                   => 'R_GW',
                                                                'bandwidth'                    => 'G.711',
                                                                'videoBandwidth'               => '384',
                                                                'lossyNetwork'                 => '',
                                                                'codecPreference'              => '',
                                                                'immersiveVideoBandwidth'      => '',
                                                                ],
                                                                [
                                                                'regionName'                   => 'R_Voicemail',
                                                                'bandwidth'                    => 'G.729',
                                                                'videoBandwidth'               => '384',
                                                                'lossyNetwork'                 => '',
                                                                'codecPreference'              => '',
                                                                'immersiveVideoBandwidth'      => '',
                                                                ],
                                                            ],
                                        ],
                ];
        // Check if the object already exists. If it isn't then add it.
        if (! empty($site_array[$TYPE])) {
            if (in_array($DATA['name'], $site_array[$TYPE])) {
                //$this->SKIP_OBJECTS[$TYPE][] = [$DATA['name'] => "Skipping... {$DATA['name']} already exists."];
                    foreach ($site_array[$TYPE] as $key => $value) {
                        if ($value == $DATA['name']) {
                            $UUID = $key;
                        }
                    }
                $OBJECT = $site_details[$TYPE][$UUID];
                $this->SKIP_OBJECTS[$TYPE][] = $OBJECT;
            } else {
                $this->ADD_OBJECTS[$TYPE][] = $DATA;
            }
        } else {
            $this->ADD_OBJECTS[$TYPE][] = $DATA;
        }

        // 8 - Add a conference bridge

        // Calculated variables
        $TYPE = 'ConferenceBridge';
        // Prepared datastructure
        $DATA = [
                'name'            => "{$SITE}_CFB",
                'description'     => "Conference bridge for {$SITE}",
                'product'         => 'Cisco IOS Enhanced Conference Bridge',
                'devicePoolName'  => "DP_{$SITE}",
                'locationName'    => "LOC_{$SITE}",
                ];
        // Check if the object already exists. If it isn't then add it.
        if (! empty($site_array[$TYPE])) {
            if (in_array($DATA['name'], $site_array[$TYPE])) {
                //$this->SKIP_OBJECTS[$TYPE][] = [$DATA['name'] => "Skipping... {$DATA['name']} already exists."];
                foreach ($site_array[$TYPE] as $key => $value) {
                    if ($value == $DATA['name']) {
                        $UUID = $key;
                    }
                }
                $OBJECT = $site_details[$TYPE][$UUID];
                $this->SKIP_OBJECTS[$TYPE][] = $OBJECT;
            } else {
                $this->ADD_OBJECTS[$TYPE][] = $DATA;
            }
        } else {
            $this->ADD_OBJECTS[$TYPE][] = $DATA;
        }

        // 9 - Add media termination point 1

        // Calculated variables
        $TYPE = 'Mtp';
        // Prepared datastructure
        $DATA = [
                'name'                 => "{$SITE}_729",
                'description'          => "G729 MTP for {$SITE}",
                'mtpType'              => 'Cisco IOS Enhanced Software Media Termination Point',
                'devicePoolName'       => "DP_{$SITE}",
                'trustedRelayPoint'    => 'false',
                ];
        // Check if the object already exists. If it isn't then add it.
        if (! empty($site_array[$TYPE])) {
            if (in_array($DATA['name'], $site_array[$TYPE])) {
                //$this->SKIP_OBJECTS[$TYPE][] = [$DATA['name'] => "Skipping... {$DATA['name']} already exists."];
                foreach ($site_array[$TYPE] as $key => $value) {
                    if ($value == $DATA['name']) {
                        $UUID = $key;
                    }
                }
                $OBJECT = $site_details[$TYPE][$UUID];
                $this->SKIP_OBJECTS[$TYPE][] = $OBJECT;
            } else {
                $this->ADD_OBJECTS[$TYPE][] = $DATA;
            }
        } else {
            $this->ADD_OBJECTS[$TYPE][] = $DATA;
        }

        // 10 - Add media termination point 2

        // Calculated variables
        $TYPE = 'Mtp';
        // Prepared datastructure
        $DATA = [
                'name'                 => "{$SITE}_711",
                'description'          => "G711 MTP for {$SITE}",
                'mtpType'              => 'Cisco IOS Enhanced Software Media Termination Point',
                'devicePoolName'       => "DP_{$SITE}",
                'trustedRelayPoint'    => 'false',
                ];
        // Check if the object already exists. If it isn't then add it.
        if (! empty($site_array[$TYPE])) {
            if (in_array($DATA['name'], $site_array[$TYPE])) {
                //$this->SKIP_OBJECTS[$TYPE][] = [$DATA['name'] => "Skipping... {$DATA['name']} already exists."];
                foreach ($site_array[$TYPE] as $key => $value) {
                    if ($value == $DATA['name']) {
                        $UUID = $key;
                    }
                }
                $OBJECT = $site_details[$TYPE][$UUID];
                $this->SKIP_OBJECTS[$TYPE][] = $OBJECT;
            } else {
                $this->ADD_OBJECTS[$TYPE][] = $DATA;
            }
        } else {
            $this->ADD_OBJECTS[$TYPE][] = $DATA;
        }

        // 11 - Add a media resource group

        // Calculated variables
        $TYPE = 'MediaResourceGroup';
        // Prepared datastructure
        $DATA = [
                'name'             => "MRG_{$SITE}",
                'description'      => "{$SITE} Media Resources",
                'multicast'        => 'false',
                'members'          => [
                                    'member' => [
                                                    [
                                                    'deviceName'    => "{$SITE}_711",
                                                    ],
                                                    [
                                                    'deviceName'    => "{$SITE}_729",
                                                    ],
                                                    [
                                                    'deviceName'    => "{$SITE}_CFB",
                                                    ],
                                                ],
                                    ],
                ];
        // Check if the object already exists. If it isn't then add it.
        if (! empty($site_array[$TYPE])) {
            if (in_array($DATA['name'], $site_array[$TYPE])) {
                //$this->SKIP_OBJECTS[$TYPE][] = [$DATA['name'] => "Skipping... {$DATA['name']} already exists."];
                foreach ($site_array[$TYPE] as $key => $value) {
                    if ($value == $DATA['name']) {
                        $UUID = $key;
                    }
                }
                $OBJECT = $site_details[$TYPE][$UUID];
                $this->SKIP_OBJECTS[$TYPE][] = $OBJECT;
            } else {
                $this->ADD_OBJECTS[$TYPE][] = $DATA;
            }
        } else {
            $this->ADD_OBJECTS[$TYPE][] = $DATA;
        }

        // 12 - Add a media resource list

        // Calculated variables
        $TYPE = 'MediaResourceList';
        // Prepared datastructure

        $DATA = [
                'name'            => "MRGL_{$SITE}",
                'members'         => [
                                    'member'    => [
                                                        [
                                                        'mediaResourceGroupName'       => "MRG_{$SITE}",
                                                        //'order'                        => '0',
                                                        ],
                                                        [
                                                        'mediaResourceGroupName'       => env('DSPFARM_MRG'),
                                                        //'order'                        => '0',
                                                        ],
                                                        [
                                                        'mediaResourceGroupName'       => 'MRG_Sub1_Resources',
                                                        //'order'                        => '1',
                                                        ],
                                                        [
                                                        'mediaResourceGroupName'       => 'MRG_Pub_Resources',
                                                        //'order'                        => '2',
                                                        ],
                                                    ],
                                    ],
                ];

        // Add the index to each member in order.
        $i = 1;
        foreach ($DATA['members']['member'] as $key => $value) {
            $value['order'] = $i++;
            $DATA['members']['member'][$key] = $value;
        }

        // Check if the object already exists. If it isn't then add it.
        if (! empty($site_array[$TYPE])) {
            if (in_array($DATA['name'], $site_array[$TYPE])) {
                //$this->SKIP_OBJECTS[$TYPE][] = [$DATA['name'] => "Skipping... {$DATA['name']} already exists."];
                foreach ($site_array[$TYPE] as $key => $value) {
                    if ($value == $DATA['name']) {
                        $UUID = $key;
                    }
                }
                $OBJECT = $site_details[$TYPE][$UUID];

                $members = $this->getMRGLMemberNames($OBJECT);

                if (in_array(env('DSPFARM_MRG'), $members)) {
                    $this->SKIP_OBJECTS[$TYPE][] = $DATA;
                } else {
                    foreach ($members as $key => $value) {

                        // Build Array to remove each partition from the CSS. - We will need to rebuild all members after this.
                        $REMOVE = $this->remove_mrg_member_to_mrgl($DATA['name'], $value, $key);
                        $this->UPDATE_OBJECTS[$TYPE.'removeMembers'][] = $REMOVE;
                    }

                    // Build Array to add partition to beginning of CSS.
                    $ADD = $this->add_mrg_member_to_mrgl($DATA['name'], "MRG_{$SITE}", $index = 1);
                    $this->UPDATE_OBJECTS[$TYPE.'addMembers'][] = $ADD;

                    // Build Array to add partition to beginning of CSS.
                    $ADD = $this->add_mrg_member_to_mrgl($DATA['name'], env('DSPFARM_MRG'), $index = 2);
                    $this->UPDATE_OBJECTS[$TYPE.'addMembers'][] = $ADD;

                    foreach ($members as $key => $value) {
                        if ($value == "MRG_{$SITE}") {
                            unset($members[$key]);
                            continue;
                        }
                        // Build Array to add partition to beginning of CSS.
                        $ADD = $this->add_mrg_member_to_mrgl($DATA['name'], $value, $index = $index + 1);
                        $this->UPDATE_OBJECTS[$TYPE.'addMembers'][] = $ADD;
                    }
                }
            } else {
                $this->ADD_OBJECTS[$TYPE][] = $DATA;
            }
        } else {
            $this->ADD_OBJECTS[$TYPE][] = $DATA;
        }

        // 13 - Add H323 Gateways

        $TYPE = 'H323Gateway';

        if ($SITE_TYPE >= 2) {
            $ROUTERMODEL = 'Cisco 2951';
            // Calculated variables

            if (! empty($H323LIST)) {
                foreach ($H323LIST as $H323IP) {
                    // Prepared datastructure
                    $DATA = [
                            'name'                         => $H323IP,
                            'description'                  => "{$SITE} {$H323IP} {$ROUTERMODEL}",
                            'callingSearchSpaceName'       => "CSS_{$SITE}_INCOMING_GW",
                            'devicePoolName'               => "DP_{$SITE}",
                            'locationName'                 => "LOC_{$SITE}",
                            'product'                      => 'H.323 Gateway',
                            'class'                        => 'Gateway',
                            'protocol'                     => 'H.225',
                            'protocolSide'                 => 'Network',
                            'signalingPort'                => '1720',
                            'tunneledProtocol'             => '',
                            'useTrustedRelayPoint'         => '',
                            'packetCaptureMode'            => '',
                            'callingPartySelection'        => '',
                            'callingLineIdPresentation'    => '',
                            'calledPartyIeNumberType'      => '',
                            'callingPartyIeNumberType'     => '',
                            'calledNumberingPlan'          => '',
                            'callingNumberingPlan'         => '',
                            'sigDigits'                    => [
                                                                '_'         => '99',
                                                                'enable'    => 'false',
                                                            ],
                            ];
                    // Check if the object already exists. If it isn't then add it.
                    if (! empty($site_array[$TYPE])) {
                        if (in_array($DATA['name'], $site_array[$TYPE])) {
                            //$this->SKIP_OBJECTS[$TYPE][] = [$DATA['name'] => "Skipping... {$DATA['name']} already exists."];
                            foreach ($site_array[$TYPE] as $key => $value) {
                                if ($value == $DATA['name']) {
                                    $UUID = $key;
                                }
                            }
                            $OBJECT = $site_details[$TYPE][$UUID];
                            $UPDATE = [];
                            if ($OBJECT['callingSearchSpaceName']['_'] != "CSS_{$SITE}_INCOMING_GW") {
                                //$UPDATE["uuid"] = $OBJECT["uuid"];
                                $UPDATE['name'] = $OBJECT['name'];
                                $UPDATE['callingSearchSpaceName'] = "CSS_{$SITE}_INCOMING_GW";
                                $this->UPDATE_OBJECTS[$TYPE][] = $UPDATE;
                            }

                            $this->SKIP_OBJECTS[$TYPE][] = $OBJECT;
                        } else {
                            $this->ADD_OBJECTS[$TYPE][] = $DATA;
                        }
                    } else {
                        $this->ADD_OBJECTS[$TYPE][] = $DATA;
                    }
                }
            }
        } else {
            if (! empty($site_array[$TYPE])) {
                foreach ($site_array[$TYPE] as $key => $value) {
                    $UUID = $key;
                }
                $OBJECT = $site_details[$TYPE][$UUID];
                $this->DELETE_OBJECTS[$TYPE][] = $OBJECT;
            }
        }

        // 14 - Add a route group
        $TYPE = 'RouteGroup';
        // Calculated variables for Site Types 2 thru 4.
        if ($SITE_TYPE >= 2) {
            // Prepared datastructure
            $i = 1;
            if (count($H323LIST) <= 1) {
                foreach ($H323LIST as $H323IP) {
                    $H323MEMBER = [
                                    'deviceName'            => $H323IP,
                                    // Increment order @ each iteration through previous loop!
                                    'deviceSelectionOrder'    => $i++,
                                    'port'                    => '0',
                                    ];
                }
                $DATA = [
                    'name'                     => "RG_{$SITE}",
                    'distributionAlgorithm'    => 'Top Down',
                    'members'                  => [
                                                'member'    => $H323MEMBER,
                                                ],
                    ];
            } else {
                $DATA = [
                    'name'                     => "RG_{$SITE}",
                    'distributionAlgorithm'    => 'Top Down',
                    'members'                  => [
                                                'member'    => [],
                                                ],
                    ];
                // Calculate multiple members to add to this array with order numbers

                foreach ($H323LIST as $H323IP) {
                    $H323MEMBER = [
                                    'deviceName'            => $H323IP,
                                    // Increment order @ each iteration through previous loop!
                                    'deviceSelectionOrder'    => $i++,
                                    'port'                    => '0',
                                    ];
                    // This is f#$%ing stupid - the array_push blows up call manager put appending without calling the function works fine.
                    //array_push($DATA['members']['member'], $H323MEMBER);
                    $DATA['members']['member'][] = $H323MEMBER;
                }
            }

            // Check if the object already exists. If it isn't then add it.
            if (! empty($site_array[$TYPE])) {
                if (in_array($DATA['name'], $site_array[$TYPE])) {
                    foreach ($site_array[$TYPE] as $key => $value) {
                        if ($value == $DATA['name']) {
                            $UUID = $key;
                        }
                    }
                    $OBJECT = $site_details[$TYPE][$UUID];
                    $this->SKIP_OBJECTS[$TYPE][] = $OBJECT;
                } else {
                    $this->ADD_OBJECTS[$TYPE][] = $DATA;
                }
            } else {
                $this->ADD_OBJECTS[$TYPE][] = $DATA;
            }
        } else {
            if (! empty($site_array[$TYPE])) {
                foreach ($site_array[$TYPE] as $key => $value) {
                    $UUID = $key;
                }
                $OBJECT = $site_details[$TYPE][$UUID];
                $this->DELETE_OBJECTS[$TYPE][] = $OBJECT;
            }
        }

        // 15 - Update an existing device pool to add the new route group above

        // Calculated variables
        $TYPE = 'DevicePool';
        // Update these fields in the device pool object for this site

        $DATA = [
                'name'                     => "DP_{$SITE}",
                'mediaResourceListName'    => "MRGL_{$SITE}",
                'localRouteGroup'          => [
                                            'name'         => 'Standard Local Route Group',
                                            'value'        => "RG_{$SITE}",
                                            ],

                ];

        if ($SITE_TYPE != 1) {
            if ((isset($SRSTIP)) && (! empty($SRSTIP))) {
                // If there is a SRST Set then you can add it to the Device Pool
                $DATA['srstName'] = "SRST_{$SITE}";
            }
        } else {
            $DATA['srstName'] = 'Disable';
        }
        // If the site type is 1  or 3 then we need to override the SLRG to be our Centralized SIP Route Group for SIP Trunking
        if (($SITE_TYPE == 1) || ($SITE_TYPE == 3)) {
            $DATA['localRouteGroup']['value'] = 'RG_CENTRAL_SBC_GRP';
        }

        if (! empty($site_array[$TYPE])) {
            if (in_array($DATA['name'], $site_array[$TYPE])) {
                foreach ($site_array[$TYPE] as $key => $value) {
                    if ($value == $DATA['name']) {
                        $UUID = $key;
                    }
                }
                $OBJECT = $site_details[$TYPE][$UUID];

                if ($OBJECT['localRouteGroup']['value'] != $DATA['localRouteGroup']['value'] || $OBJECT['srstName']['_'] != $DATA['srstName']) {
                    $this->UPDATE_OBJECTS[$TYPE][] = $DATA;
                } else {
                    $this->SKIP_OBJECTS[$TYPE][] = $OBJECT;
                }
            }
        }

        // 16 - Update our translation patterns for the site.

        // Calculated variables
        $TYPE = 'TransPattern';

        // Prepare and add datastructures
        foreach ($site_details[$TYPE] as $TRANS) {
            // Update Transpattern Partitions and Device Pools
            $PATTERN = $TRANS['pattern'];

            if ($TRANS['routePartitionName']['_'] != "PT_{$SITE}_XLATE" || $TRANS['callingSearchSpaceName']['_'] != "CSS_{$SITE}_DEVICE") {
                $DATA = [
                    'routePartitionName'               => "PT_{$SITE}_XLATE",
                    'pattern'                          => $PATTERN,
                    'callingSearchSpaceName'           => "CSS_{$SITE}_DEVICE",
                    'usage'                            => 'Translation',
                    ];
                $this->UPDATE_OBJECTS[$TYPE][] = $DATA;
            } else {
                $this->SKIP_OBJECTS[$TYPE][] = $TRANS;
            }
        }

        // 17 - Create Called Party Transformations.
        $TYPE = 'CalledPartyTransformationPattern';

        if (($SITE_TYPE == 2) || ($SITE_TYPE == 4)) {
            $DATA = [
                [
                    // Local Calling via E.164 dialing. Can add multiple NPAs here if needed but would need to be manual.
                    'pattern'                           => "\+1.{$NPA}[2-9]XXXXXX",
                    'description'                       => 'digits sent to gw or session boarder controller',
                    'routePartitionName'                => "PT_{$SITE}_GW_CALLED_XFORM",
                    'digitDiscardInstructionName'       => 'predot',
                    'calledPartyPrefixDigits'           => '9',
                ],
                [
                    // Leveraging a pri and needs to send 91+ 10 digits to h323 gateway
                    'pattern'                           => '\+.1[2-9]XX[2-9]XXXXXX',
                    'description'                       => 'digits sent to gw or session boarder controller',
                    'routePartitionName'                => "PT_{$SITE}_GW_CALLED_XFORM",
                    'digitDiscardInstructionName'       => 'predot',
                    'calledPartyPrefixDigits'           => '9',
                ],
            ];

            foreach ($DATA as $OBJECT) {
                if (! empty($site_array[$TYPE])) {
                    if (in_array($OBJECT['pattern'], $site_array[$TYPE])) {
                        //$this->SKIP_OBJECTS[$TYPE][] = "{$TYPE} Skipping... {$OBJECT['pattern']} already exists.";
                        //$this->SKIP_OBJECTS[$TYPE][] = [$OBJECT['pattern'] => "Skipping... {$OBJECT['pattern']} already exists."];
                        foreach ($site_array[$TYPE] as $key => $value) {
                            $UUID = false;
                            //print_r($key);
                            if ($value == $OBJECT['pattern']) {
                                $UUID = $key;
                                if ($UUID) {
                                    $OBJECT = $site_details[$TYPE][$UUID];
                                    $this->SKIP_OBJECTS[$TYPE][] = $OBJECT;
                                }
                            }
                        }
                    } else {
                        $this->ADD_OBJECTS[$TYPE][] = $OBJECT;
                    }
                } else {
                    $this->ADD_OBJECTS[$TYPE][] = $OBJECT;
                }
            }
        } else {
            // Delete them.
            foreach ($site_array[$TYPE] as $key => $value) {
                $UUID = $key;
                $OBJECT = $site_details[$TYPE][$UUID];
                if ($OBJECT['routePartitionName']['_'] == "PT_{$SITE}_GW_CALLED_XFORM") {
                    $this->DELETE_OBJECTS[$TYPE][] = $OBJECT;
                }
            }
        }

        // 18 - Create our Route Lists
        //Create 911 Route Lists

        // Calculated variables
        $TYPE = 'RouteList';
        // Build Array of Route List
        $DATA = [
                    'name'                        => "RL_{$SITE}_911",
                    'description'                 => "{$SITE} - 911 Calling Route List",
                    'callManagerGroupName'        => "CMG-{$SITE}",
                    'routeListEnabled'            => true,
                    'runOnEveryNode'              => true,

                    'members'                    => [
                                                        'member' => [
                                                                    'routeGroupName'                         => "RG_{$SITE}",
                                                                    'selectionOrder'                         => 1,
                                                                    'useFullyQualifiedCallingPartyNumber'    => 'Default',
                                                                    ],
                                                    ],
                ];

        if ($SITE_TYPE >= 3) {

            // Check if the object already exists. If it isn't then add it.
            if (! empty($site_array[$TYPE])) {
                if (in_array($DATA['name'], $site_array[$TYPE])) {
                    //$this->SKIP_OBJECTS[$TYPE][] = [$DATA['name'] => "Skipping... {$DATA['name']} already exists."];
                    foreach ($site_array[$TYPE] as $key => $value) {
                        if ($value == $DATA['name']) {
                            $UUID = $key;
                        }
                    }
                    $OBJECT = $site_details[$TYPE][$UUID];
                    $this->SKIP_OBJECTS[$TYPE][] = $OBJECT;
                } else {
                    $this->ADD_OBJECTS[$TYPE][] = $DATA;
                }
            } else {
                $this->ADD_OBJECTS[$TYPE][] = $DATA;
            }
        } else {
            // Delete them.
            foreach ($site_array[$TYPE] as $key => $value) {
                $UUID = $key;
                $OBJECT = $site_details[$TYPE][$UUID];
                if ($OBJECT['name'] == $DATA['name']) {
                    $this->DELETE_OBJECTS[$TYPE][] = $OBJECT;
                }
            }
        }

        // 18 - Create our 911 Route Patterns

        // Calculated variables
        $TYPE = 'RoutePattern';

        // Build Array of Route List

        $PATTERNS = [
                    [
                        'pattern'                     => '911',
                        'description'                 => "{$SITE} 911 - Emergency Services",
                        'routePartitionName'          => "PT_{$SITE}_911",
                        'blockEnable'                 => 'false',
                        'useCallingPartyPhoneMask'    => 'Default',
                        'networkLocation'             => 'OffNet',
                        //"routeFilterName"			=> "",
                        'patternUrgency'            => 'false',

                        'destination'                    => [
                                                            'routeListName' => "RL_{$SITE}_911",

                                                        ],
                    ],
                    [
                        'pattern'                     => '9.911',
                        'description'                 => "{$SITE} 911 - Emergency Services",
                        'routePartitionName'          => "PT_{$SITE}_911",
                        'blockEnable'                 => 'false',
                        'useCallingPartyPhoneMask'    => 'Default',
                        'networkLocation'             => 'OffNet',
                        //"routeFilterName"			=> "",
                        'patternUrgency'            => 'false',

                        'destination'                    => [
                                                            'routeListName' => "RL_{$SITE}_911",

                                                        ],
                    ],
        ];

        // Insert Patterns to cause a delay after dialling 911 - This waits for the T302 Timer to expire before sending the call to Emergency Services
        // This has been used to reduce misdaialing 911 callling. T302 Timer in Service Parameters has been reduced to 4000 ms.
        // Set the Environmental Variable to true to use this setting. Default is false.
        if (env('CUCM_911_T302_DELAY')) {
            $PATTERNS[] = [
                        'pattern'                        => '911!',
                        'description'                    => "{$SITE} 911 - Emergency Services - T302 Delay",
                        'routePartitionName'             => "PT_{$SITE}_911",
                        'blockEnable'                    => 'true',
                        'useCallingPartyPhoneMask'       => 'Default',
                        'networkLocation'                => 'OffNet',
                        'patternUrgency'                 => 'false',
                        'destination'                    => [
                                                            'routeListName' => "RL_{$SITE}_911",

                                                        ],
                    ];

            $PATTERNS[] = [
                        'pattern'                        => '9.911!',
                        'description'                    => "{$SITE} 9911 - Emergency Services - T302 Delay",
                        'routePartitionName'             => "PT_{$SITE}_911",
                        'blockEnable'                    => 'true',
                        'useCallingPartyPhoneMask'       => 'Default',
                        'networkLocation'                => 'OffNet',
                        'patternUrgency'                 => 'false',
                        'destination'                    => [
                                                            'routeListName' => "RL_{$SITE}_911",

                                                        ],
                    ];
        }
        if ($SITE_TYPE >= 3) {
            // Add each pattern in the array.
            foreach ($PATTERNS as $DATA) {
                // Check if the object already exists. If it isn't then add it.
                if (! empty($site_array[$TYPE])) {
                    if (in_array($DATA['pattern'], $site_array['RoutePattern'])) {
                        //$this->SKIP_OBJECTS[$TYPE][] = [$DATA['name'] => "Skipping... {$DATA['name']} already exists."];
                        foreach ($site_array[$TYPE] as $key => $value) {
                            if ($value == $DATA['pattern']) {
                                $UUID = $key;
                            }
                        }

                        $OBJECT = $site_details[$TYPE][$UUID];
                        $this->SKIP_OBJECTS[$TYPE][] = $OBJECT;
                    } else {
                        $this->ADD_OBJECTS[$TYPE][] = $DATA;
                    }
                } else {
                    $this->ADD_OBJECTS[$TYPE][] = $DATA;
                }
            }
        } else {
            // If not design type 3 or above - Delete them.
            foreach ($PATTERNS as $DATA) {
                // Check if the object already exists. If it isn't then add it.
                if (! empty($site_array[$TYPE])) {
                    if (in_array($DATA['pattern'], $site_array['RoutePattern'])) {
                        //$this->SKIP_OBJECTS[$TYPE][] = [$DATA['name'] => "Skipping... {$DATA['name']} already exists."];
                        foreach ($site_array[$TYPE] as $key => $value) {
                            if ($value == $DATA['pattern']) {
                                $UUID = $key;
                            }
                        }

                        $OBJECT = $site_details[$TYPE][$UUID];
                        $this->DELETE_OBJECTS[$TYPE][] = $OBJECT;
                    }
                }
            }
        }

        // 19 - Update CTI Route Points

        // Calculated variables
        $TYPE = 'CtiRoutePoint';

        // Check if the object already exists. If it isn't then add it.
        if (! empty($site_array[$TYPE])) {
            $UPDATE = [];
            foreach ($site_array[$TYPE] as $key => $value) {
                $UUID = $key;
                $OBJECT = $site_details[$TYPE][$UUID];

                // Update the CTI Route Point CSS
                if ($OBJECT['callingSearchSpaceName']['_'] != "CSS_{$SITE}_DEVICE") {
                    $UPDATE['name'] = $OBJECT['name'];
                    $UPDATE['description'] = $OBJECT['description'];
                    $UPDATE['callingSearchSpaceName'] = "CSS_{$SITE}_DEVICE";
                    $this->UPDATE_OBJECTS[$TYPE][] = $UPDATE;
                }
            }
        }

        // Define Delete Order.
        $DELETEORDER = ['CalledPartyTransformationPattern',
                    'CallingPartyTransformationPattern',
                    'TransPattern',
                    'updateDevicePool',
                    'RoutePattern',
                    'RouteList',
                    'RouteGroup',
                    'H323Gateway',
                    'MediaResourceList',
                    'MediaResourceGroup',
                    'Mtp',
                    'ConferenceBridge',
                    'DevicePool',
                    'CallManagerGroup',
                    'Region',
                    'Location',
                    'Css',
                    'RoutePartition',
                    'Srst',
                    ];

        // Reorder our Delete Array in the required order.
        $REORDER = [];
        foreach ($DELETEORDER as $STEP) {
            if (isset($this->DELETE_OBJECTS[$STEP])) {
                $REORDER[$STEP] = $this->DELETE_OBJECTS[$STEP];
            }
        }

        $this->DELETE_OBJECTS = $REORDER;

        $return = ['type'        => $SITE_TYPE,
                    'changes'    => [
                                        'Add'                   => $this->ADD_OBJECTS,
                                        'Update'                => $this->UPDATE_OBJECTS,
                                        'Delete'                => $this->DELETE_OBJECTS,
                                        'Skip'                  => $this->SKIP_OBJECTS,
                                        'CurrentDetails'        => $site_details,
                                        'CurrentSummary'        => $site_array,
                                    ],
                    ];

        return $return;
    }

    public function run_migration(Request $request)
    {
        $verb = $request->verb;
        $migrations = $request->migration;
        $result = [];
        foreach ($migrations as $TYPE => $ARRAY) {
            if ($TYPE == 'CssremoveMembers' || $TYPE == 'CssaddMembers') {
                $TYPE = 'Css';                                                                                // Update the Type to an acutal type for the updates of CSS members.
            }
            if ($TYPE == 'MediaResourceListremoveMembers' || $TYPE == 'MediaResourceListaddMembers') {
                $TYPE = 'MediaResourceList';                                                                // Update the Type to an actual type for the updates of MRGL members.
            }
            foreach ($ARRAY as $DATA) {
                if ($verb == 'Add') {
                    $this->wrap_add_object($DATA, $TYPE);
                }

                if ($verb == 'Update') {
                    if (isset($DATA['name'])) {
                        $PRIMARYKEY = $DATA['name'];
                    } elseif (isset($DATA['pattern'])) {
                        $PRIMARYKEY = $DATA['pattern'];
                    }
                    // Run the update operation
                    try {
                        //print "Attempting to update object type {$TYPE} for {$SITE}:";
                        $REPLY = $this->cucm->update_object_type_by_assoc($DATA, $TYPE);
                        $NEW = $this->cucm->get_object_type_by_uuid($REPLY, $TYPE);
                        //$this->results['DevicePoolUpdate'] = "{$TYPE} UPDATED: {$REPLY}";
                        $this->results["{$TYPE}_Update"][] = [
                                                                    'type'        => $TYPE,
                                                                    'object'      => $PRIMARYKEY,
                                                                    'status'      => 'success',
                                                                    'reply'       => $REPLY,
                                                                    'request'     => $DATA,
                                                                    'new'         => $NEW,

                                                                ];
                    } catch (\Exception $E) {
                        $EXCEPTION = "Exception updating object type {$TYPE}".
                              "{$E->getMessage()}";
                        $this->results[$TYPE][] = [
                                                    'type'             => $TYPE,
                                                    'object'           => $PRIMARYKEY,
                                                    'status'           => 'error',
                                                    'reply'            => $EXCEPTION,
                                                    'request'          => $DATA,
                                                ];
                    }
                }

                if ($verb == 'Delete') {
                    $UUID = $DATA['uuid'];
                    if (isset($DATA['name'])) {
                        $PRIMARYKEY = $DATA['name'];
                    } elseif (isset($DATA['pattern'])) {
                        $PRIMARYKEY = $DATA['pattern'];
                    }
                    try {
                        //print "Attempting to update object type {$TYPE} for {$SITE}:";
                        $REPLY = $this->cucm->delete_object_type_by_uuid($UUID, $TYPE);
                        //$this->results['DevicePoolUpdate'] = "{$TYPE} UPDATED: {$REPLY}";
                        $this->results["{$TYPE}_Update"][] = [
                                                                    'type'       => $TYPE,
                                                                    'object'     => $PRIMARYKEY,
                                                                    'status'     => 'success',
                                                                    'reply'      => $REPLY,
                                                                    'request'    => $DATA,

                                                                ];
                    } catch (\Exception $E) {
                        $EXCEPTION = "Exception updating object type {$TYPE}".
                              "{$E->getMessage()}";
                        $this->results[$TYPE][] = [
                                                    'type'             => $TYPE,
                                                    'object'           => $PRIMARYKEY,
                                                    'status'           => 'error',
                                                    'reply'            => $EXCEPTION,
                                                    'request'          => $DATA,
                                                ];
                    }
                }
            }
        }
        $response = [
            'status_code'    => 200,
            'success'        => true,
            'message'        => '',
            'response'       => $this->results,
            ];

        // Create log entry
        //activity('cucm_provisioning_log')->causedBy($user)->withProperties(['function' => __FUNCTION__, 'response' => $response])->log('add site');

        return response()->json($response);
    }
}
