<?php

namespace App\Http\Controllers;

// Add Dummy CUCM class for permissions use for now.
use App\Cucmclass;
use Illuminate\Http\Request;
// Include the JWT Facades shortcut
use Tymon\JWTAuth\Facades\JWTAuth;
// Activity Logger
use Spatie\Activitylog\Models\Activity;

class Cucm extends Controller
{
    public function __construct()
    {
        // Only authenticated users can make these calls
        $this->middleware('jwt.auth');

        // Construct new cucm object
        $this->cucm = new \CallmanagerAXL\Callmanager(env('CALLMANAGER_URL'),
                                                    storage_path(env('CALLMANAGER_WSDL')),
                                                    env('CALLMANAGER_USER'),
                                                    env('CALLMANAGER_PASS')
                                                    );
    }

    // Variable to return to user
    public $results;

    public function start_ldap_sync()
    {
        $user = JWTAuth::parseToken()->authenticate();
        // Check user permissions
        if (! $user->can('update', Cucmclass::class)) {
            abort(401, 'You are not authorized');
        }

        try {
            $ldapsync = $this->cucm->do_ldap_sync(env('CALLMANAGER_LDAP_NAME'), 'true');

            // Create log entry
            activity('cucm_provisioning_log')->causedBy($user)->withProperties(['function' => __FUNCTION__])->log($ldapsync->return);

            return $ldapsync->return.PHP_EOL;
        } catch (\Exception $e) {
            return 'Callmanager blew uP: '.$e->getMessage().PHP_EOL;
        }
    }

    public function stop_ldap_sync()
    {
        $user = JWTAuth::parseToken()->authenticate();
        // Check user permissions
        if (! $user->can('update', Cucmclass::class)) {
            abort(401, 'You are not authorized');
        }

        try {
            $ldapsync = $this->cucm->do_ldap_sync(env('CALLMANAGER_LDAP_NAME'), 'false');

            // Create log entry
            activity('cucm_provisioning_log')->causedBy($user)->withProperties(['function' => __FUNCTION__])->log($ldapsync->return);

            return $ldapsync->return.PHP_EOL;
        } catch (\Exception $e) {
            return 'Callmanager blew uP: '.$e->getMessage().PHP_EOL;
        }
    }

    public function get_ldap_sync_status()
    {
        $user = JWTAuth::parseToken()->authenticate();
        // Check user permissions
        if (! $user->can('update', Cucmclass::class)) {
            abort(401, 'You are not authorized');
        }

        try {
            $ldapsync = $this->cucm->get_ldap_sync_status(env('CALLMANAGER_LDAP_NAME'));

            // Create log entry
            activity('cucm_provisioning_log')->causedBy($user)->withProperties(['function' => __FUNCTION__])->log($ldapsync->return);

            return $ldapsync->return.PHP_EOL;
        } catch (\Exception $e) {
            echo 'Callmanager blew uP: '.$e->getMessage().PHP_EOL;
        }
    }

    // CUCM Add Wrapper
    public function wrap_add_object($DATA, $TYPE)
    {
        $user = JWTAuth::parseToken()->authenticate();
        // Check user permissions
        if (! $user->can('update', Cucmclass::class)) {
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

            $this->results[$TYPE][] = $LOG;

            // Create log entry
            activity('cucm_provisioning_log')->causedBy($user)->withProperties($LOG)->log('add object');

            return $REPLY;
        } catch (\Exception $E) {
            $EXCEPTION = "Exception adding object type: {$TYPE}".
                  "{$E->getMessage()}";
                  /*"Stack trace:\n".
                  "{$E->getTraceAsString()}".
                  "Data sent:\n";*/
            //$delimiter = "Stack trace:";
            //explode ($delimiter , $EXCEPTION);
            $this->results[$TYPE][] = [
                                        'type'             => $TYPE,
                                        'object'           => $OBJECT,
                                        'status'           => 'error',
                                        'reply'            => $EXCEPTION,
                                        'request'          => $DATA,
                                    ];
        }
    }

    public function listCssDetails(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        // Check user permissions
        if (! $user->can('read', Cucmclass::class)) {
            abort(401, 'You are not authorized');
        }

        try {
            $list = $this->cucm->get_object_type_by_site('%', 'Css');

            if (! count($list)) {
                throw new \Exception('Indexed results from call mangler is empty');
            }
        } catch (\Exception $e) {
            echo 'Callmanager blew up: '.$e->getMessage().PHP_EOL;
            dd($e->getTrace());
        }

        $CSS_LIST = [];
        foreach ($list as $key => $value) {
            $UUID = $key;

            try {
                $css = $this->cucm->get_object_type_by_uuid($UUID, 'Css');

                if (! count($css)) {
                    throw new \Exception('Indexed results from call mangler is empty');
                }
            } catch (\Exception $e) {
                echo 'Callmanager blew up: '.$e->getMessage().PHP_EOL;
                dd($e->getTrace());
            }

            $CSS_LIST[] = $css;
            //$CSS_LIST[] = ;
        }

        $response = [
                    'status_code'    => 200,
                    'success'        => true,
                    'message'        => '',
                    'response'       => $CSS_LIST,
                    ];

        return response()->json($response);
    }

    public function listDateTimeGroup(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        // Check user permissions
        if (! $user->can('read', Cucmclass::class)) {
            abort(401, 'You are not authorized');
        }

        $user = JWTAuth::parseToken()->authenticate();

        try {
            $list = $this->cucm->get_object_type_by_site('%', 'DateTimeGroup');

            if (! count($list)) {
                throw new \Exception('Indexed results from call mangler is empty');
            }
        } catch (\Exception $e) {
            echo 'Callmanager blew up: '.$e->getMessage().PHP_EOL;
            dd($e->getTrace());
        }

        /*
        $CSS_LIST = [];
        foreach ($list as $key => $value) {
            $UUID = $key;

            try {
                $css = $this->cucm->get_object_type_by_uuid($UUID, 'DateTimeGroup');

                if (! count($css)) {
                    throw new \Exception('Indexed results from call mangler is empty');
                }
            } catch (\Exception $e) {
                echo 'Callmanager blew up: '.$e->getMessage().PHP_EOL;
                dd($e->getTrace());
            }

            $CSS_LIST[] = $css;
            //$CSS_LIST[] = ;
        }
        */

        $response = [
                    'status_code'    => 200,
                    'success'        => true,
                    'message'        => '',
                    'response'       => $list,
                    ];

        return response()->json($response);
    }

    public function listCssDetailsbyName(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        // Check user permissions
        if (! $user->can('read', Cucmclass::class)) {
            abort(401, 'You are not authorized');
        }

        $user = JWTAuth::parseToken()->authenticate();

        $name = $request->name;

        try {
            $css = $this->cucm->get_object_type_by_name($name, 'Css');

            if (! count($css)) {
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
                    'response'       => $css,
                    ];

        return response()->json($response);
    }

    public function listRoutePatternsByPartition(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        // Check user permissions
        if (! $user->can('read', Cucmclass::class)) {
            abort(401, 'You are not authorized');
        }

        try {
            $result = $this->cucm->get_object_type_by_site($request->routePartitionName, 'RoutePattern');

            if (! count($result)) {
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
                    'response'       => $result,
                    ];

        return response()->json($response);
    }

    public function getObjectTypebySite(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        // Check user permissions
        if (! $user->can('read', Cucmclass::class)) {
            abort(401, 'You are not authorized');
        }

        //return $request;
        //echo $request->sitecode;
        try {
            $result = $this->cucm->get_object_type_by_site($request->sitecode, $request->type);

            if (! count($result)) {
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
                    'response'       => $result,
                    ];

        return response()->json($response);
    }

    public function getObjectTypebyName(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        // Check user permissions
        if (! $user->can('read', Cucmclass::class)) {
            abort(401, 'You are not authorized');
        }

        try {
            $result = $this->cucm->get_object_type_by_name($request->name, $request->type);

            if (! count($result)) {
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
                    'response'       => $result,
                    ];

        return response()->json($response);
    }

    public function getObjectTypebyUUID(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        // Check user permissions
        if (! $user->can('read', Cucmclass::class)) {
            abort(401, 'You are not authorized');
        }

        try {
            $result = $this->cucm->get_object_type_by_uuid($request->uuid, $request->type);

            if (! count($result)) {
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
                    'response'       => $result,
                    ];

        return response()->json($response);
    }

    public function getNumberbyRoutePlan(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        // Check user permissions
        if (! $user->can('read', Cucmclass::class)) {
            abort(401, 'You are not authorized');
        }

        try {
            $result = $this->cucm->get_route_plan_by_name($request->number);

            if (! count($result)) {
                throw new \Exception('Indexed results from call mangler is empty');
            }
        } catch (\Exception $e) {
            //echo 'Callmanager blew up: '.$e->getMessage().PHP_EOL;
            //dd($e->getTrace());
        }

        $response = [
                    'status_code'    => 200,
                    'success'        => true,
                    'message'        => '',
                    'response'       => $result,
                    ];

        return response()->json($response);
    }

    public function getNumberandDeviceDetailsbyRoutePlan(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        // Check user permissions
        if (! $user->can('read', Cucmclass::class)) {
            abort(401, 'You are not authorized');
        }

        try {
            $result = [];
            $numbers = $this->cucm->get_route_plan_by_name($request->number);

            if (count($numbers)) {
                $result['numbers'] = $numbers;
                $result['uuid'] = $numbers[0]['uuid'];
                $result['line_details'] = $this->cucm->get_object_type_by_uuid($result['uuid'], 'Line');

                foreach ($numbers as $number) {
                    $phone = $this->cucm->get_object_type_by_name($number['routeDetail'], 'Phone');
                    $phone['line_details'] = $this->cucm->get_lines_details_by_phone_name($phone['name']);
                    $result['device_details'][] = $phone;
                }
            }

            if (! count($result)) {
                throw new \Exception('Indexed results from call mangler is empty');
            }
        } catch (\Exception $e) {
            //echo 'Callmanager blew up: '.$e->getMessage().PHP_EOL;
            //dd($e->getTrace());
        }

        $response = [
                    'status_code'    => 200,
                    'success'        => true,
                    'message'        => '',
                    'response'       => $result,
                    ];

        return response()->json($response);
    }

    // Get a list of Sites by device pools.
    protected function getSites()
    {
        echo 'Getting sites from CUCM...'.PHP_EOL;
        try {
            $sites = $this->cucm->get_site_names();
            //$sites = ["KHONEOMA"];

            // Array of DP we don't want to include.
            $discard = ['TEST', 'Self_Provisioning', 'ECD', '911Enable', 'ATT_SIP', 'Travis', 'CENCOLIT', 'TEMPLATE'];

            if (! $sites) {
                // Return blank array if no results in $didinfo.
                echo 'didinfo is blank!';

                return $sites;
            }
            foreach ($sites as $key => $site) {
                // Get rid of the crap we don't want.
                if (in_array($site, $discard)) {
                    echo 'Discarding: '.$site.PHP_EOL;
                    unset($sites[$key]);
                }
            }

            if (! count($sites)) {
                throw new \Exception('Indexed results from call mangler is empty');
            }

            return $sites;
        } catch (\Exception $e) {
            echo 'Callmanager blew uP: '.$e->getMessage().PHP_EOL;
            dd($e->getTrace());
        }
    }

    /*
    protected function getPartitions()
    {
        $TYPE = 'RoutePartition';
        $SITE = '';
        echo 'Getting partitions from CUCM...'.PHP_EOL;
        try {

            $partitions = $this->cucm->get_object_type_by_site($SITE, $TYPE);
            unset($cucm);

            return $partitions;
        } catch (\Exception $e) {
            echo 'Callmanager blew uP: '.$e->getMessage().PHP_EOL;
            dd($e->getTrace());
        }
    }


    protected function getCss($SITE)
    {
        $TYPE = 'Css';
        $SITE = '';
        echo 'Getting Css from CUCM...'.PHP_EOL;
        try {

            $css = $this->cucm->get_object_type_by_site($SITE, $TYPE);
            unset($cucm);

            return $css;
        } catch (\Exception $e) {
            echo 'Callmanager blew uP: '.$e->getMessage().PHP_EOL;
            dd($e->getTrace());
        }
    }
    */

    protected function getCssDetails($css)
    {
        $TYPE = 'Css';

        $RESULTS = [];
        foreach ($css as $key => $value) {
            echo "Getting CSS Details for {$value} from CUCM...".PHP_EOL;
            try {
                $UUID = $key;
                //print "UUID: ".$UUID;

                $css_details = $this->cucm->get_object_type_by_uuid($UUID, $TYPE);

                //print_r($css_details);
                $RESULTS[] = $css_details;
            } catch (\Exception $e) {
                echo 'Callmanager blew uP: '.$e->getMessage().PHP_EOL;
                dd($e->getTrace());
            }
        }

        return $RESULTS;

        unset($cucm);
    }

    // Get a summary of all types supported by site.
     protected function getSiteDetailsbySite($SITE)
     {
         try {
             $site_details = $this->cucm->get_all_object_types_by_site($SITE);
         } catch (\Exception $e) {
             echo 'Callmanager blew uP: '.$e->getMessage().PHP_EOL;
         }

         return $site_details;
     }

    //
    protected function getCssMembers($css_details)
    {
        $RESULTS = [];
        foreach ($css_details as $css) {
            // Exract Member Partitions for each Css
            $CSS = [];
            //$CSS[$css['name']] = $css['name'];
            foreach ($css['members'] as $member) {
                //print "Member: ".PHP_EOL;
                //print_r($member);
                $MEMBERS = [];
                if (is_array($member)) {
                    foreach ($member as $partition) {
                        //print_r($partition);
                        $MEMBER = [];
                        //print_r($partition['routePartitionName']['_']);

                        if (isset($partition['routePartitionName'])) {
                            $MEMBER['name'] = $partition['routePartitionName']['_'];
                            $MEMBER['index'] = $partition['index'];
                        }

                        // Append Member to Members with the key as the index number.
                        $MEMBERS[$MEMBER['index']] = $MEMBER;
                    }
                }
            }

            // Append CSS Members to Results with Name as Key.
            $RESULTS[$css['name']] = $MEMBERS;
        }

        return $RESULTS;
    }

    protected function getCssMembersbyCSS($css)
    {
        $RESULTS = [];

        if ($css['partitionUsage'] == 'Intercom') {
            return $RESULTS;
        }

        if ($css['partitionUsage'] == 'General') {
            foreach ($css['members'] as $member) {
                //print "Member: ".PHP_EOL;
                    //print_r($member);
                    $MEMBERS = [];
                if (is_array($member)) {
                    foreach ($member as $partition) {
                        //print_r($partition);
                        $MEMBER = [];

                        if (isset($partition['routePartitionName'])) {
                            $MEMBER['name'] = $partition['routePartitionName']['_'];
                            $MEMBER['index'] = $partition['index'];

                            //echo $partition['routePartitionName']['_'];
                        } else {
                            return $RESULTS;
                        }

                            // Append Member to Members with the key as the index number.
                            $MEMBERS[$MEMBER['index']] = $MEMBER;
                    }
                }
            }

                // Append CSS Members to Results with Name as Key.
                //print_r($MEMBERS);
            if (! empty($MEMBERS)) {
                $RESULTS[$css['name']] = $MEMBERS;
            }
        }

        return $RESULTS;
    }

    protected function getCssMemberNamesbyCSS($css)
    {
        $RESULTS = [];

        if ($css['partitionUsage'] == 'Intercom') {
            return $RESULTS;
        }

        if ($css['partitionUsage'] == 'General') {
            foreach ($css['members'] as $member) {
                //print "Member: ".PHP_EOL;
                //print_r($member);
                $MEMBERS = [];
                if (is_array($member)) {
                    foreach ($member as $partition) {
                        //print_r($partition);
                        if (isset($partition['_'])) {
                            $MEMBER = $partition['_'];
                            $MEMBERS[$partition['index']] = $MEMBER;
                        } elseif (isset($partition['routePartitionName'])) {
                            $MEMBER = $partition['routePartitionName']['_'];

                            $MEMBERS[$partition['index']] = $MEMBER;                    // Append Member to Members with the key as the index number.
                        }
                    }
                }
            }

                // Append CSS Members to Results with Name as Key.
                //print_r($MEMBERS);
            if (! empty($MEMBERS)) {
                $RESULTS = $MEMBERS;
            }
        }

        return $RESULTS;
    }

    protected function getMRGLMemberNames($mrgl)
    {
        $RESULTS = [];

        foreach ($mrgl['members'] as $member) {
            //print "Member: ".PHP_EOL;
                //print_r($member);
                $MEMBERS = [];
            if (is_array($member)) {
                foreach ($member as $mrg) {
                    if (isset($mrg['_'])) {
                        $MEMBER = $mrg['_'];
                        $MEMBERS[$mrg['order']] = $MEMBER;
                    } elseif (isset($mrg['mediaResourceGroupName'])) {
                        $MEMBER = $mrg['mediaResourceGroupName']['_'];
                        $MEMBERS[$mrg['order']] = $MEMBER;
                    } else {
                        return $RESULTS;
                    }
                }
            }
        }

            // Append mrgl Members to Results with Name as Key.
            //print_r($MEMBERS);
        if (! empty($MEMBERS)) {
            $RESULTS = $MEMBERS;
        }

        return $RESULTS;
    }

    protected function compare_changes($array1, $array2)
    {
        $RESULTS = [];

        // Do a compare of the start and end of the site array after the changes.
            foreach ($array1 as $key => $value) {
                //print_r($value);

                $RESULTS[$key] = array_diff_assoc($value, $site_details_after[$key]);
            }
        //print_r($RESULTS);

        return $RESULTS;
    }

    // Guild Route List with SLRG as the member.
    protected function add_new_911_routelist_for_site($SITE, $SLRG)
    {
        $TYPE = 'routeList';

        $DATA = [
            'name'                            => "RL_{$SITE}_911",
            'description'                     => "{$SITE} 911 Route List",
            'callManagerGroupName'            => "CMG_{$SITE}",
            'routeListEnabled'                => 'true',
            'members'                         => ['member' => ['routeGroupName'     => $SLRG,
                                                                'selectionOrder'    => 1,
                                                            ],
                                                ],

            ];

        //echo "Building Site 911 Route List with {$SLRG} in CUCM...".PHP_EOL;
        try {
            // Add Partion
            $partitions = $this->cucm->add_object_type_by_assoc($DATA, $TYPE);
            //print_r($partitions);

            return $partitions;
        } catch (\Exception $e) {
            echo 'Callmanager blew uP: '.$e->getMessage().PHP_EOL;
            dd($e->getTrace());
        }

        unset($cucm);
    }

    // Add Route Patterns
    protected function build_new_partitions_for_site($SITE)
    {
        //echo 'Building Site partitions Array...'.PHP_EOL;

        // Build Array of Patitions
        $DATA = [
                    [
                        'name'                            => 'PT_'.$SITE.'_911',
                        'description'                     => $SITE.' 911 Calling',
                        'useOriginatingDeviceTimeZone'    => 'true',
                    ],
                ];

        return $DATA;
    }

    protected function add_partition_index_number($PARTITION, $CSS_NEXT_INDEX)
    {
        // Build Array of CSS adding new Partition with index of 15.
        $DATA = [
                            'routePartitionName'       => $PARTITION,
                            'index'                    => $CSS_NEXT_INDEX,
                ];

        return $DATA;
    }

    // Add Route Patterns
    protected function add_partition_member_to_css($CSS, $PARTITION, $INDEX)
    {
        //echo 'Building Site partitions Array...'.PHP_EOL;

        // Build Array of CSS adding new Partition with index of 15.
        $DATA = [
                    'name'                => $CSS,
                    'addMembers'          => [
                                                'member' => [
                                                            'routePartitionName'       => $PARTITION,
                                                            'index'                    => $INDEX,
                                                            ],
                                            ],
                ];

        return $DATA;
    }

    // Add Route Patterns
    protected function remove_partition_member_to_css($CSS, $PARTITION, $INDEX)
    {
        //echo 'Building Site partitions Array...'.PHP_EOL;

        // Build Array of CSS adding new Partition with index of 15.
        $DATA = [
                    'name'                   => $CSS,
                    'removeMembers'          => [
                                                'member' => [
                                                            'routePartitionName'       => $PARTITION,
                                                            'index'                    => $INDEX,
                                                            ],
                                            ],
                ];

        return $DATA;
    }

    protected function add_mrg_member_to_mrgl($MRGL, $MRG, $ORDER)
    {
        // Build Array of MRGL adding new MRG.
        $DATA = [
                    'name'                => $MRGL,
                    'addMembers'          => [
                                                'member' => [
                                                            'mediaResourceGroupName'           => $MRG,
                                                            'order'                            => $ORDER,
                                                            ],
                                            ],
                ];

        return $DATA;
    }

    protected function remove_mrg_member_to_mrgl($MRGL, $MRG, $ORDER)
    {
        // Build Array of MRGL adding new MRG.
        $DATA = [
                    'name'                   => $MRGL,
                    'removeMembers'          => [
                                                'member' => [
                                                            'mediaResourceGroupName'           => $MRG,
                                                            'order'                            => $ORDER,
                                                            ],
                                            ],
                ];

        return $DATA;
    }

    // Add 911 Route List
    protected function build_new_911_routelist_array($SITE, $CCMGRP, $SLRG)
    {
        //echo "Building {$SITE} 911 Route List Array...".PHP_EOL;

        // Build Array of Route List
        $DATA = [
                    'name'                        => "RL_{$SITE}_911",
                    'description'                 => "{$SITE} - 911 Calling Route List",
                    'callManagerGroupName'        => $CCMGRP,
                    'routeListEnabled'            => true,
                    'runOnEveryNode'              => true,

                    'members'                    => [
                                                        'member' => [
                                                                    'routeGroupName'                         => $SLRG,
                                                                    'selectionOrder'                         => 1,
                                                                    'useFullyQualifiedCallingPartyNumber'    => 'Default',
                                                                    ],
                                                    ],
                ];

        return $DATA;
    }

    // Add 911 Route Patterns
    protected function build_new_911_routepatterns_array($SITE)
    {
        //echo 'Building Site 911 Route Patterns Array...'.PHP_EOL;

        $DATA = [
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

        return $DATA;
    }
}
