<?php

namespace App\Http\Controllers;

// Add Dummy CUCM class for permissions use for now.
use App\Cucmclass;
use Illuminate\Http\Request;
// Include the JWT Facades shortcut
use Tymon\JWTAuth\Facades\JWTAuth;

class Cucmsite extends Cucm
{
    public function listsites()
    {
        $user = JWTAuth::parseToken()->authenticate();
        // Check user permissions
        if (! $user->can('read', Cucmclass::class)) {
            abort(401, 'You are not authorized');
        }

        try {
            $sites = $this->cucm->get_site_names();

            if (! count($sites)) {
                throw new \Exception('Indexed results from call mangler is empty');
            }
        } catch (\Exception $e) {
            echo 'Callmanager blew up: '.$e->getMessage().PHP_EOL;
            dd($e->getTrace());
        }

        $response = [
                    'status_code'    => 200,
                    'success'        => true,
                    'message'        => '',
                    'response'       => $sites,
                    ];

        return response()->json($response);
    }

    public function getSite(Request $request, $name)
    {
        $user = JWTAuth::parseToken()->authenticate();
        // Check user permissions
        if (! $user->can('read', Cucmclass::class)) {
            abort(401, 'You are not authorized');
        }

        $name = strtoupper($name);

        try {
            $site = $this->cucm->get_all_object_types_by_site($name);
            if (! count($site['DevicePool'])) {
                $site = '0';
                //throw new \Exception('Indexed results from call mangler is empty');
            }
        } catch (\Exception $e) {
            echo 'Callmanager blew up: '.$e->getMessage().PHP_EOL;
            dd($e->getTrace());
        }

        $response = [
                    'status_code'    => 200,
                    'success'        => true,
                    'message'        => '',
                    'response'       => $site,
                    ];

        return response()->json($response);
    }

    public function getSiteDetails(Request $request, $name)
    {
        $user = JWTAuth::parseToken()->authenticate();
        // Check user permissions
        if (! $user->can('read', Cucmclass::class)) {
            abort(401, 'You are not authorized');
        }

        $name = strtoupper($name);

        try {
            $site = $this->cucm->get_all_object_type_details_by_site($name);
            if (! count($site)) {
                throw new \Exception('Indexed results from call mangler is empty');
            }
        } catch (\Exception $e) {
            echo 'Callmanager blew up: '.$e->getMessage().PHP_EOL;
            dd($e->getTrace());
        }

        $response = [
                    'status_code'    => 200,
                    'success'        => true,
                    'message'        => '',
                    'response'       => $site,
                    ];

        return response()->json($response);
    }

    public function createSite(Request $request)
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

        $SITE_TYPE = $request->type;

        // If the SRST IP is set, has contents, and validates as an IP address
        if (isset($request->srstip) && ! filter_var($request->srstip, FILTER_VALIDATE_IP)) {
            return 'Error: SRST invalid';
        } elseif (isset($request->srstip)) {
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
                return "Error, one of the H323 IPs provided is not valid: {$H323IP}";
            }
        }
        $H323LIST = array_values($H323LIST);

        // Check their timezone
        if (! isset($request->timezone) || ! $request->timezone) {
            return 'Error, no timezone selected';
        }
        $TIMEZONE = $request->timezone;

        // Check their NPA
        if (! isset($request->npa) || ! $request->npa) {
            return 'Error, no npa selected';
        }
        $NPA = $request->npa;

        // Check their NXX
        if (! isset($request->nxx) || ! $request->nxx) {
            return 'Error, no nxx selected';
        }
        $NXX = $request->nxx;

        // Turn the users text into an array of translation patterns
        $DIDTEXT = '';
        if (isset($request->didrange) && $request->didrange) {
            $DIDTEXT = $request->didrange;
        } else {
            return 'No DID ranges provided';
        }
        //$DIDLIST = preg_split('/\r\n|\r|\n/', $DIDTEXT);
        $DIDLIST = explode(',', $DIDTEXT);
        if (! count($DIDLIST)) {
            return 'No DID ranges found';
        }
        // Loop through translation pattern DID sections and validate them against callmanagers data dictionary
        foreach ($DIDLIST as $KEY => $DID) {
            // Trim off any whitespace around the DID range
            $DID = trim($DID);
            $DIDLIST[$KEY] = $DID;
            // If the line is blank rip it out of the list
            if (! $DID) {
                unset($DIDLIST[$KEY]);
                continue;
            }
            // If the line has content but is NOT a valid DID thing, then abort
            $REGEX = '/^[]0-9X[-]{4,14}$/'; // This is from CUCM 10.5's data dictionary... and modified
            if (! preg_match($REGEX, $DID)) {
                return "Error, one of the DID ranges provided is not valid: {$DID}";
            }
        }
        $DIDLIST = array_values($DIDLIST);

        // Check for an optional operator extension
        $OPERATOR = '';
        if (isset($request->operator) && $request->operator) {
            $OPERATOR = $request->operator;
        }

        // Use user input to decide what CUCM subscribers to home this new site to

        // If the users site code is KHO, dump them on our subscribers
        $SITECODE = strtoupper($request->sitecode);
        if (substr($SITECODE, 0, 2) == 'KHO') {
            $CUCM1 = 'KHONEMDCVCS02';
            $CUCM2 = 'KHONESDCVCS06';
        // Otherwise if they are KOS, dump them there
        } elseif (substr($SITECODE, 0, 2) == 'KOS') {
            $CUCM1 = 'KHONESDCVCS04';
            $CUCM2 = 'KHONEMDCVCS05';
        // Otherwise if they are EAST or CENTRAL time
        } elseif (preg_match('/(eastern|central)+/i', $TIMEZONE)) {
            $CUCM1 = 'KHONEMDCVCS01';
            $CUCM2 = 'KHONESDCVCS06';
        } else {
            $CUCM1 = 'KHONESDCVCS03';
            $CUCM2 = 'KHONEMDCVCS05';
        }

        /*
        // Setup for testing.
        $result = [
                    $SITECODE,
                    $SITE_TYPE,
                    $SRSTIP,
                    $H323LIST,
                    $TIMEZONE,
                    $NPA,
                    $NXX,
                    $DIDLIST,
                    $CUCM1,
                    $CUCM2,
                    $OPERATOR
                ];
        */

        // Final user information required to provision a CUCM SITE:
        $result = $this->provision_cucm_site_axl(
                                                $SITECODE,
                                                $SITE_TYPE,
                                                $SRSTIP,
                                                $H323LIST,
                                                $TIMEZONE,
                                                $NPA,
                                                $NXX,
                                                $DIDLIST,
                                                $CUCM1,
                                                $CUCM2,
                                                $OPERATOR
                                                );

        $response = [
            'status_code'    => 200,
            'success'        => true,
            'message'        => '',
            'response'       => $result,
            ];

        // Create log entry
        activity('cucm_provisioning_log')->causedBy($user)->withProperties(['function' => __FUNCTION__, 'response' => $response])->log('add site');

        return response()->json($response);
    }

    // Build all the elements needed for the site.

    private function provision_cucm_site_axl(
                                                $SITE,
                                                $SITE_TYPE,
                                                $SRSTIP,
                                                $H323LIST,
                                                $TIMEZONE,
                                                $NPA,
                                                $NXX,
                                                $DIDLIST,
                                                $CUCM1,
                                                $CUCM2,
                                                $OPERATOR
                                            ) {

        // Check if the site exists in the CUCM database first.
        $site_array = $this->cucm->get_all_object_types_by_site($SITE);

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
                    $this->results[$TYPE][] = "Skipping... {$DATA['name']} already exists.";
                } else {
                    $this->wrap_add_object($DATA, $TYPE);
                }
            } else {
                $this->wrap_add_object($DATA, $TYPE);
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
                    $this->results[$TYPE][] = "Skipping... {$DATA['name']} already exists.";
                } else {
                    $this->wrap_add_object($DATA, $TYPE);
                }
            } else {
                $this->wrap_add_object($DATA, $TYPE);
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
            //print_r($DATA);
            // Check if the object already exists. If it isn't then add it.
            if (! empty($site_array[$TYPE])) {
                if (in_array($DATA['name'], $site_array[$TYPE])) {
                    $this->results[$TYPE][] = "Skipping... {$DATA['name']} already exists.";
                } else {
                    $this->wrap_add_object($DATA, $TYPE);
                }
            } else {
                $this->wrap_add_object($DATA, $TYPE);
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
                $this->results[$TYPE][] = "Skipping... {$DATA['name']} already exists.";
            } else {
                $this->wrap_add_object($DATA, $TYPE);
            }
        } else {
            $this->wrap_add_object($DATA, $TYPE);
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
                $this->results[$TYPE][] = "Skipping... {$DATA['name']} already exists.";
            } else {
                $this->wrap_add_object($DATA, $TYPE);
            }
        } else {
            $this->wrap_add_object($DATA, $TYPE);
        }

        // 6 - Add a call mangler group

        // Calculated variables
        $TYPE = 'CallManagerGroup';
        // Prepared datastructure
        $DATA = [
                'name'        => "CMG-{$SITE}",
                'members'     => [
                                'member' => [
                                                [
                                                'callManagerName'     => $CUCM1,
                                                'priority'            => '1',
                                                ],
                                                [
                                                'callManagerName'     => $CUCM2,
                                                'priority'            => '2',
                                                ],
                                            ],
                                ],
                ];
        // Check if the object already exists. If it isn't then add it.
        if (! empty($site_array[$TYPE])) {
            if (in_array($DATA['name'], $site_array[$TYPE])) {
                $this->results[$TYPE][] = "Skipping... {$DATA['name']} already exists.";
            } else {
                $this->wrap_add_object($DATA, $TYPE);
            }
        } else {
            $this->wrap_add_object($DATA, $TYPE);
        }

        // 7 - Add a device pool

        // Calculated variables
        $TYPE = 'DevicePool';
        // Prepared datastructure
        $DATA = [
                'name'                    => "DP_{$SITE}",
                'dateTimeSettingName'     => $TIMEZONE,
                'callManagerGroupName'    => "CMG-{$SITE}",
                'regionName'              => "R_{$SITE}",
                'srstName'                => 'Disable',
                'locationName'            => "LOC_{$SITE}",
                ];

        if ((isset($SRSTIP)) && (! empty($SRSTIP))) {
            // If there is a SRST Set then you can add it to the Device Pool
            $DATA['srstName'] = "SRST_{$SITE}";
        }

        // Check if the object already exists. If it isn't then add it.
        if (! empty($site_array[$TYPE])) {
            if (in_array($DATA['name'], $site_array[$TYPE])) {
                $this->results[$TYPE][] = "Skipping... {$DATA['name']} already exists.";
            } else {
                $this->wrap_add_object($DATA, $TYPE);
            }
        } else {
            $this->wrap_add_object($DATA, $TYPE);
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
                $this->results[$TYPE][] = "Skipping... {$DATA['name']} already exists.";
            } else {
                $this->wrap_add_object($DATA, $TYPE);
            }
        } else {
            $this->wrap_add_object($DATA, $TYPE);
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
                $this->results[$TYPE][] = "Skipping... {$DATA['name']} already exists.";
            } else {
                $this->wrap_add_object($DATA, $TYPE);
            }
        } else {
            $this->wrap_add_object($DATA, $TYPE);
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
                $this->results[$TYPE][] = "Skipping... {$DATA['name']} already exists.";
            } else {
                $this->wrap_add_object($DATA, $TYPE);
            }
        } else {
            $this->wrap_add_object($DATA, $TYPE);
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
                $this->results[$TYPE][] = "Skipping... {$DATA['name']} already exists.";
            } else {
                $this->wrap_add_object($DATA, $TYPE);
            }
        } else {
            $this->wrap_add_object($DATA, $TYPE);
        }

        // 12 - Add a media resource list

        // Calculated variables
        $TYPE = 'MediaResourceList';
        // Prepared datastructure

        if ($SITE_TYPE == 4) {
            $DATA = [
                'name'            => "MRGL_{$SITE}",
                'members'         => [
                                    'member'    => [
                                                        [
                                                        'mediaResourceGroupName'       => "MRG_{$SITE}",
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
        } else {
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
        }

        // Add the index to each member in order.
        $i = 1;
        foreach ($DATA['members']['member'] as $key => $value) {
            $value['order'] = $i++;
            $DATA['members']['member'][$key] = $value;
        }

        // Check if the object already exists. If it isn't then add it.
        if (! empty($site_array[$TYPE])) {
            if (in_array($DATA['name'], $site_array[$TYPE])) {
                $this->results[$TYPE][] = "Skipping... {$DATA['name']} already exists.";
            } else {
                $this->wrap_add_object($DATA, $TYPE);
            }
        } else {
            $this->wrap_add_object($DATA, $TYPE);
        }

        if ($SITE_TYPE >= 2) {

            // 13 - Add H323 Gateways
            $ROUTERMODEL = 'Cisco 2951';
            // Calculated variables
            $TYPE = 'H323Gateway';

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
                            $this->results[$TYPE][] = "Skipping... {$DATA['name']} already exists.";
                        } else {
                            $this->wrap_add_object($DATA, $TYPE);
                        }
                    } else {
                        $this->wrap_add_object($DATA, $TYPE);
                    }
                }
            }
        }

        // 14 - Add a route group

        // Calculated variables for Site Types 2 thru 4.
        if ($SITE_TYPE >= 2) {
            $TYPE = 'RouteGroup';
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
                    $this->results[$TYPE][] = "Skipping... {$DATA['name']} already exists.";
                } else {
                    $this->wrap_add_object($DATA, $TYPE);
                }
            } else {
                $this->wrap_add_object($DATA, $TYPE);
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

        // If the site type is 1  or 3 then we need to override the SLRG to be our Centralized SIP Route Group for SIP Trunking
        if (($SITE_TYPE == 1) || ($SITE_TYPE == 3)) {
            $DATA['localRouteGroup']['value'] = 'RG_CENTRAL_SBC_GRP';
        }
        // Run the update operation
        try {
            //print "Attempting to update object type {$TYPE} for {$SITE}:";
            $REPLY = $this->cucm->update_object_type_by_assoc($DATA, $TYPE);
            //$this->results['DevicePoolUpdate'] = "{$TYPE} UPDATED: {$REPLY}";
            $this->results['DevicePoolUpdate'][] = [
                                                        'type'       => $TYPE,
                                                        'object'     => $DATA['name'],
                                                        'status'     => 'success',
                                                        'reply'      => $REPLY,
                                                        'request'    => $DATA,

                                                    ];
        } catch (\Exception $E) {
            $EXCEPTION = "Exception updating object type {$TYPE} for site {$SITE}:".
                  "{$E->getMessage()}";
                  /*"Stack trace:\n".
                  "{$E->getTraceAsString()}".
                  "Data sent:\n";*/
            //$DATA[$TYPE]['exception'] = $EXCEPTION;
            //$this->results[$TYPE][] = $DATA;
            $this->results[$TYPE][] = [
                                        'type'             => $TYPE,
                                        'object'           => $DATA['name'],
                                        'status'           => 'error',
                                        'reply'            => $EXCEPTION,
                                        'request'          => $DATA,
                                    ];
        }

        // 16 - Create our translation patterns from user input

        // Calculated variables
        $TYPE = 'TransPattern';

        // Prepare and add datastructures
        foreach ($DIDLIST as $PATTERN) {
            $DATA = [
                    'routePartitionName'               => "PT_{$SITE}_XLATE",
                    'pattern'                          => $PATTERN,
                    'calledPartyTransformationMask'    => "{$NPA}{$NXX}XXXX",
                    'callingSearchSpaceName'           => "CSS_{$SITE}_DEVICE",
                    'description'                      => "{$SITE} dial pattern {$PATTERN}",
                    'usage'                            => 'Translation',
                    ];
            // Check if the object already exists. If it isn't then add it.
            if (! empty($site_array[$TYPE])) {
                if (in_array($DATA['pattern'], $site_array[$TYPE])) {
                    $this->results[$TYPE][] = "Skipping... {$DATA['pattern']} already exists.";
                } else {
                    $this->wrap_add_object($DATA, $TYPE);
                }
            } else {
                $this->wrap_add_object($DATA, $TYPE);
            }

            $DATA = [
                    'routePartitionName'               => "PT_{$SITE}_XLATE",
                    'pattern'                          => "*{$PATTERN}",
                    'calledPartyTransformationMask'    => "*{$NPA}{$NXX}XXXX",
                    'callingSearchSpaceName'           => "CSS_{$SITE}_DEVICE",
                    'description'                      => "{$SITE} voicemail pattern {$PATTERN}",
                    'usage'                            => 'Translation',
                    ];
            // Check if the object already exists. If it isn't then add it.
            if (! empty($site_array[$TYPE])) {
                if (in_array($DATA['pattern'], $site_array[$TYPE])) {
                    $this->results[$TYPE][] = "Skipping... {$DATA['pattern']} already exists.";
                } else {
                    $this->wrap_add_object($DATA, $TYPE);
                }
            } else {
                $this->wrap_add_object($DATA, $TYPE);
            }
        }

        if ((isset($OPERATOR)) && (! empty($OPERATOR))) {
            // Create Operator Translation Patterns.
            $DATA = [
                    'routePartitionName'               => "PT_{$SITE}_XLATE",
                    'pattern'                          => '0',
                    'calledPartyTransformationMask'    => "{$OPERATOR}",
                    'callingSearchSpaceName'           => "CSS_{$SITE}_DEVICE",
                    'description'                      => "{$SITE} dial pattern Operator {$OPERATOR}",
                    'usage'                            => 'Translation',
                    ];
            // Check if the object already exists. If it isn't then add it.
            if (! empty($site_array[$TYPE])) {
                if (in_array($DATA['pattern'], $site_array[$TYPE])) {
                    $this->results[$TYPE][] = "Skipping... {$DATA['pattern']} already exists.";
                } else {
                    $this->wrap_add_object($DATA, $TYPE);
                }
            } else {
                $this->wrap_add_object($DATA, $TYPE);
            }

            $DATA = [
                    'routePartitionName'               => "PT_{$SITE}_XLATE",
                    'pattern'                          => '*0',
                    'calledPartyTransformationMask'    => "*{$OPERATOR}",
                    'callingSearchSpaceName'           => "CSS_{$SITE}_DEVICE",
                    'description'                      => "{$SITE} dial pattern Operator Voicemail *{$OPERATOR}",
                    'usage'                            => 'Translation',
                    ];
            // Check if the object already exists. If it isn't then add it.
            if (! empty($site_array[$TYPE])) {
                if (in_array($DATA['pattern'], $site_array[$TYPE])) {
                    $this->results[$TYPE][] = "Skipping... {$DATA['pattern']} already exists.";
                } else {
                    $this->wrap_add_object($DATA, $TYPE);
                }
            } else {
                $this->wrap_add_object($DATA, $TYPE);
            }
        }

        // 17 - Create Called Party Transformations.

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

            $TYPE = 'CalledPartyTransformationPattern';

            foreach ($DATA as $OBJECT) {
                // Get a list of all current objects by type to use to see what is exists now.
                try {
                    $objects = $this->cucm->get_object_type_by_site($OBJECT['routePartitionName'], $TYPE);
                } catch (\Exception $E) {
                    echo 'Exception Getting CalledPartyTransformationPattern from CUCM:'.
                          "{$E->getMessage()}".
                          "Stack trace:\n".
                          "{$E->getTraceAsString()}".
                          "Data sent:\n";
                }

                if (! empty($objects)) {
                    if (in_array($OBJECT['pattern'], $objects)) {
                        $result[$TYPE][] = "{$TYPE} Skipping... {$OBJECT['pattern']} already exists.";
                    } else {
                        $result[$TYPE][] = $this->wrap_add_object($OBJECT, $TYPE);
                    }
                } else {
                    $result[$TYPE][] = $this->wrap_add_object($OBJECT, $TYPE);
                }
            }
        }

        // 18 - Create our Route Lists

        if ($SITE_TYPE >= 3) {

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
            // Check if the object already exists. If it isn't then add it.
            if (! empty($site_array[$TYPE])) {
                if (in_array($DATA['name'], $site_array[$TYPE])) {
                    $this->results[$TYPE][] = "Skipping... {$DATA['name']} already exists.";
                } else {
                    $this->wrap_add_object($DATA, $TYPE);
                }
            } else {
                $this->wrap_add_object($DATA, $TYPE);
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
            //print_r($PATTERNS);
            }

            // Add each pattern in the array.
            foreach ($PATTERNS as $DATA) {
                // Check if the object already exists. If it isn't then add it.
                if (! empty($site_array[$TYPE])) {
                    if (in_array($DATA['pattern'], $site_array['RoutePattern'])) {
                        $this->results[$TYPE][] = "Skipping... {$DATA['pattern']} already exists.";
                        continue;
                    } else {
                        $this->wrap_add_object($DATA, $TYPE);
                    }
                } else {
                    $this->wrap_add_object($DATA, $TYPE);
                }
            }
        }

        return $this->results;
    }
}
