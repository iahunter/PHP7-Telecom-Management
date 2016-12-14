<?php

namespace App\Console\Commands\CallManager;

use Illuminate\Console\Command;

class BuildCUCMSiteDefaults extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'callmanager:sitedefaults';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'DO NOT RUN!!! Custom Script - Builds All Site Default Dependencies for Globalized Dialplan and Normalization. Also blocked translations and such. ';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        // Construct new cucm object
        $this->cucm = new \CallmanagerAXL\Callmanager(env('CALLMANAGER_URL'),
                                                    storage_path(env('CALLMANAGER_WSDL')),
                                                    env('CALLMANAGER_USER'),
                                                    env('CALLMANAGER_PASS')
                                                    );

        parent::__construct();
    }

    public $results;    // Array of results to return to user.

    /**
     * Wrap CUCM Object adds with this wrapper.
     *
     * @return mixed
     */
    public function wrap_add_object($DATA, $TYPE)
    {
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
            $result = "{$TYPE} CREATED: {$REPLY}\n\n";
        } catch (\Exception $E) {
            $EXCEPTION = "Exception adding object type {$OBJECT}:".
                      "{$E->getMessage()}".
                      "Stack trace:\n".
                      "{$E->getTraceAsString()}".
                      "Data sent:\n";
            $result = $EXCEPTION;
        }

        return $result;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->results[] = $this->addGlobalPartitions();
        $this->results[] = $this->addGlobalCss();
        $this->results[] = $this->addGlobalRoutePartitions();
        $this->results[] = $this->addBlockRoutePartitions();
        $this->results[] = $this->addApplicationDialRules();
        $this->results[] = $this->addCallingPartyTransformationPatterns();
        $this->results[] = $this->addCalledPartyTransformationPatterns();

        /********************************************************************

            * Prebuilt - Universal-Route_List Points to the Standard Local Route Group.

            You need to manually add the SIP Trunks for your SBCs.
            Then create a new Route Group named 'RG_CENTRAL_SBC_GRP' with the SIP trunks pointing to your SBC IPs as the only members.
            This will be used by Site Device Pools later when adding sites.

        /********************************************************************/

        print_r($this->results);
    }

    public function addGlobalPartitions()
    {
        $TYPE = 'RoutePartition';

        // Get a list of all current objects by type to use to see what is exists now.
        try {
            $objects = $this->cucm->get_object_type_by_site('%', $TYPE);
        } catch (\Exception $E) {
            echo 'Exception Getting RoutePartitions from CUCM:'.
                  "{$E->getMessage()}".
                  "Stack trace:\n".
                  "{$E->getTraceAsString()}".
                  "Data sent:\n";
        }

        // Prepared datastructure
        $DATA = [

                    [
                    // Existing Phone Lines
                    'name'                            => 'Global-All-Lines',
                    'description'                     => 'Cluster-Wide Phone PT',
                    'useOriginatingDeviceTimeZone'    => 'true',
                    ],
                    [
                    // Existing Voicemail
                    'name'                            => 'System-Voicemail',
                    'description'                     => 'Voicemail Port/Pilot PT',
                    'useOriginatingDeviceTimeZone'    => 'true',
                    ],

                    // Add new Global Partitions
                    [
                    'name'                            => 'PT_GLOBAL_SVC',
                    'description'                     => 'Cluster-Wide Service PT',
                    'useOriginatingDeviceTimeZone'    => 'true',
                    ],
                    [
                    'name'                            => 'PT_GLOBAL_XLATE',
                    'description'                     => 'Cluster-Wide Transalation Pattern',
                    'useOriginatingDeviceTimeZone'    => 'true',
                    ],
                    [
                    'name'                            => 'PT_PSTN_LOCAL_10_DIGIT',
                    'description'                     => 'Cluster-Wide local dialing partition',
                    'useOriginatingDeviceTimeZone'    => 'true',
                    ],
                    [
                    'name'                            => 'PT_PSTN_TOLLFREE',
                    'description'                     => 'Cluster-Wide Toll Free dialing parition',
                    'useOriginatingDeviceTimeZone'    => 'true',
                    ],
                    [
                    'name'                            => 'PT_PSTN_LD',
                    'description'                     => 'Cluster-Wide Long Distance dialing parition',
                    'useOriginatingDeviceTimeZone'    => 'true',
                    ],
                    [
                    'name'                            => 'PT_PSTN_INTL',
                    'description'                     => 'Cluster-Wide Internaltional dialing parition',
                    'useOriginatingDeviceTimeZone'    => 'true',
                    ],
                    [
                    'name'                            => 'PT_911Enable',
                    'description'                     => '911 calls routed to 911 Enable EGW',
                    'useOriginatingDeviceTimeZone'    => 'true',
                    ],

                    // Add Block Partitions
                    [
                    'name'                            => 'PT_BLOCK_FRAUD',
                    'description'                     => 'Cluster Wide Block',
                    'useOriginatingDeviceTimeZone'    => 'true',
                    ],
                    [
                    'name'                            => 'PT_BLOCK_SUBSCRIBE',
                    'description'                     => 'Clusterwide Block Subscribe Msg',
                    'useOriginatingDeviceTimeZone'    => 'true',
                    ],
                    [
                    'name'                            => 'PT_BLOCK_INTL',
                    'description'                     => 'Clusterwide Block Intl',
                    'useOriginatingDeviceTimeZone'    => 'true',
                    ],
                    [
                    'name'                            => 'PT_BLOCK_LD',
                    'description'                     => 'Clusterwide Block LD',
                    'useOriginatingDeviceTimeZone'    => 'true',
                    ],
                    [
                    'name'                            => 'PT_BLOCK_TOLLFREE',
                    'description'                     => 'Clusterwide Block TollFree',
                    'useOriginatingDeviceTimeZone'    => 'true',
                    ],
                    [
                    'name'                            => 'PT_BLOCK_LOCAL',
                    'description'                     => 'Clusterwide Block Local',
                    'useOriginatingDeviceTimeZone'    => 'true',
                    ],

                    // Add Global Called/Calling Transformation Partitions
                    [
                    'name'                            => 'PT_GLOBAL_GW_INCOMING_CALLING_XFORM',
                    'description'                     => 'Clusterwide GW calling party Xform',
                    'useOriginatingDeviceTimeZone'    => 'true',
                    ],
                    [
                    'name'                            => 'PT_GLOBAL_GW_OUTGOING_CALLING_XFORM',
                    'description'                     => 'Clusterwide GW calling party Xform',
                    'useOriginatingDeviceTimeZone'    => 'true',
                    ],
                    [
                    'name'                            => 'PT_GLOBAL_GW_CALLED_XFORM',
                    'description'                     => 'Clusterwide GW called party Xform',
                    'useOriginatingDeviceTimeZone'    => 'true',
                    ],
                ];
        $result = [];
        // Check if the object already exists. If it isn't then add it.
        foreach ($DATA as $PARTITION) {
            if (! empty($objects)) {
                if (in_array($PARTITION['name'], $objects)) {
                    $result[$TYPE][] = "{$TYPE} Skipping... {$PARTITION['name']} already exists.";
                } else {
                    $result[$TYPE][] = $this->wrap_add_object($PARTITION, $TYPE);
                }
            } else {
                $result[$TYPE][] = $this->wrap_add_object($PARTITION, $TYPE);
            }
        }

        return $result;
    }

    public function addGlobalCss()
    {
        $TYPE = 'Css';

        // Get a list of objects by type to use to see what is exists now.
        try {
            $objects = $this->cucm->get_object_type_by_site('%', $TYPE);
        } catch (\Exception $E) {
            echo 'Exception Getting RoutePartitions from CUCM:'.
                  "{$E->getMessage()}".
                  "Stack trace:\n".
                  "{$E->getTraceAsString()}".
                  "Data sent:\n";
        }

        // 1. Add a Global Css

        // Calculated variables
        $TYPE = 'Css';
        // Prepared datastructure
        $DATA = [
                    [
                    'name'            => 'CSS_LINEONLY_L1_INTERNAL',
                    'description'     => 'Line only CSS for device with internal only access',
                    'members'         => [
                                        'member' => [
                                                        [
                                                        'routePartitionName'   => 'PT_BLOCK_FRAUD',
                                                        'index'                => 1,
                                                        ],
                                                        [
                                                        'routePartitionName'   => 'PT_BLOCK_INTL',
                                                        'index'                => 2,
                                                        ],
                                                        [
                                                        'routePartitionName'   => 'PT_BLOCK_LD',
                                                        'index'                => 3,
                                                        ],
                                                        [
                                                        'routePartitionName'   => 'PT_BLOCK_TOLLFREE',
                                                        'index'                => 4,
                                                        ],
                                                        [
                                                        'routePartitionName'   => 'PT_BLOCK_LOCAL',
                                                        'index'                => 5,
                                                        ],
                                                    ],
                                        ],
                    ],
                    [
                    'name'            => 'CSS_LINEONLY_L2_LOCAL',
                    'description'     => 'Line only CSS for device with local and internal',
                    'members'         => [
                                        'member' => [
                                                        [
                                                        'routePartitionName'   => 'PT_BLOCK_FRAUD',
                                                        'index'                => 1,
                                                        ],
                                                        [
                                                        'routePartitionName'   => 'PT_BLOCK_INTL',
                                                        'index'                => 2,
                                                        ],
                                                        [
                                                        'routePartitionName'   => 'PT_BLOCK_LD',
                                                        'index'                => 3,
                                                        ],
                                                    ],
                                        ],
                    ],
                    [
                    'name'            => 'CSS_LINEONLY_L3_LD',
                    'description'     => 'Line only CSS for device with long distance',
                    'members'         => [
                                        'member' => [
                                                        [
                                                        'routePartitionName'   => 'PT_BLOCK_FRAUD',
                                                        'index'                => 1,
                                                        ],
                                                        [
                                                        'routePartitionName'   => 'PT_BLOCK_INTL',
                                                        'index'                => 2,
                                                        ],
                                                    ],
                                        ],
                    ],
                    [
                    'name'            => 'CSS_LINEONLY_L4_INTL',
                    'description'     => 'Line only CSS for device with International access',
                    'members'         => [
                                        'member' => [
                                                        [
                                                        'routePartitionName'   => 'PT_BLOCK_FRAUD',
                                                        'index'                => 1,
                                                        ],
                                                    ],
                                        ],
                    ],
                    [
                    'name'            => 'CSS_LINEONLY_L5_UNRESTRICTED',
                    'description'     => 'Line only CSS for device with Unrestricted access',
                    'members'         => [
                                        'member' => [

                                                    ],
                                        ],
                    ],

                    // Incoming CSSs
                    [
                    'name'            => 'CSS_SIP_TRUNK_INCOMING',
                    'description'     => 'applied to incoming CSS on SIP trunk carrier',
                    'members'         => [
                                        'member' => [
                                                        [
                                                        'routePartitionName'   => 'Global-All-Lines',
                                                        'index'                => 1,
                                                        ],
                                                        [
                                                        'routePartitionName'   => 'System-Voicemail',
                                                        'index'                => 2,
                                                        ],
                                                        [
                                                        'routePartitionName'   => 'PT_GLOBAL_SVC',
                                                        'index'                => 3,
                                                        ],
                                                        [
                                                        'routePartitionName'   => 'PT_GLOBAL_XLATE',
                                                        'index'                => 4,
                                                        ],
                                                    ],
                                        ],
                    ],
					
					// Transformation CSSs
                    [
                    'name'            => 'CSS_GLOBAL_GW_INCOMING_CALLING_XFORM',
                    'description'     => 'inbound direction on SIP trunk or GW from PSTN',
                    'members'         => [
                                        'member' => [
                                                        [
                                                        'routePartitionName'   => 'PT_GLOBAL_GW_INCOMING_CALLING_XFORM',
                                                        'index'                => 1,
                                                        ],
                                                    ],
                                        ],
                    ],

                    // Transformation CSSs
                    [
                    'name'            => 'CSS_GLOBAL_GW_OUTGOING_CALLING_XFORM',
                    'description'     => 'outbound direction on SIP trunk or GW from PSTN',
                    'members'         => [
                                        'member' => [
                                                        [
                                                        'routePartitionName'   => 'PT_GLOBAL_GW_OUTGOING_CALLING_XFORM',
                                                        'index'                => 1,
                                                        ],
                                                    ],
                                        ],
                    ],
                    [
                    'name'            => 'CSS_GLOBAL_GW_CALLED_XFORM',
                    'description'     => 'outbound direction on centralized SIP trunk',
                    'members'         => [
                                        'member' => [
                                                        [
                                                        'routePartitionName'   => 'PT_GLOBAL_GW_CALLED_XFORM',
                                                        'index'                => 1,
                                                        ],
                                                    ],
                                        ],
                    ],

                    // Subscribe CSSs
                    [
                    'name'            => 'CSS_DEVICE_SUBSCRIBE',
                    'description'     => 'assigned to all phones Subscribe CSS',
                    'members'         => [
                                        'member' => [
                                                        [
                                                        'routePartitionName'   => 'Global-All-Lines',
                                                        'index'                => 1,
                                                        ],
                                                    ],
                                        ],
                    ],
                    [
                    'name'            => 'CSS_BLOCK_SUBSCRIBE',
                    'description'     => 'applied to sip trunks and gateway subscribe css',
                    'members'         => [
                                        'member' => [
                                                        [
                                                        'routePartitionName'   => 'PT_BLOCK_SUBSCRIBE',
                                                        'index'                => 1,
                                                        ],
                                                    ],
                                        ],
                    ],

                    // Global Internal CSS
                    [
                    'name'            => 'CSS_GLOBAL_INTERNAL_ONLY',
                    'description'     => 'applied to translation patterns rerouting, css etc',
                    'members'         => [
                                        'member' => [
                                                        [
                                                        'routePartitionName'   => 'Global-All-Lines',
                                                        'index'                => 1,
                                                        ],
                                                        [
                                                        'routePartitionName'   => 'System-Voicemail',
                                                        'index'                => 2,
                                                        ],
                                                        [
                                                        'routePartitionName'   => 'PT_GLOBAL_SVC',
                                                        'index'                => 3,
                                                        ],
                                                        [
                                                        'routePartitionName'   => 'PT_GLOBAL_XLATE',
                                                        'index'                => 4,
                                                        ],
                                                    ],
                                        ],
                    ],

                    // Call Forward CSSs
                    [
                    'name'            => 'CSS_LINE_CFWD_INTERNAL',
                    'description'     => "CSS applied to DN CALLFW CSS's internally only",
                    'members'         => [
                                        'member' => [
                                                        [
                                                        'routePartitionName'   => 'Global-All-Lines',
                                                        'index'                => 1,
                                                        ],
                                                        [
                                                        'routePartitionName'   => 'System-Voicemail',
                                                        'index'                => 2,
                                                        ],
                                                        [
                                                        'routePartitionName'   => 'PT_GLOBAL_SVC',
                                                        'index'                => 3,
                                                        ],
                                                        [
                                                        'routePartitionName'   => 'PT_GLOBAL_XLATE',
                                                        'index'                => 4,
                                                        ],
                                                    ],
                                        ],
                    ],
                    [
                    'name'            => 'CSS_LINE_CFWD_LOCAL_10_DIGIT',
                    'description'     => "CSS applied to DN CALLFW CSS's locally only",
                    'members'         => [
                                        'member' => [
                                                        [
                                                        'routePartitionName'   => 'Global-All-Lines',
                                                        'index'                => 1,
                                                        ],
                                                        [
                                                        'routePartitionName'   => 'System-Voicemail',
                                                        'index'                => 2,
                                                        ],
                                                        [
                                                        'routePartitionName'   => 'PT_GLOBAL_SVC',
                                                        'index'                => 3,
                                                        ],
                                                        [
                                                        'routePartitionName'   => 'PT_GLOBAL_XLATE',
                                                        'index'                => 4,
                                                        ],
                                                        [
                                                        'routePartitionName'   => 'PT_PSTN_LOCAL_10_DIGIT',
                                                        'index'                => 5,
                                                        ],
                                                    ],
                                        ],
                    ],
                    [
                    'name'            => 'CSS_LINE_CFWD_LD',
                    'description'     => "CSS applied to DN CALLFW CSS's locally only",
                    'members'         => [
                                        'member' => [
                                                        [
                                                        'routePartitionName'   => 'Global-All-Lines',
                                                        'index'                => 1,
                                                        ],
                                                        [
                                                        'routePartitionName'   => 'System-Voicemail',
                                                        'index'                => 2,
                                                        ],
                                                        [
                                                        'routePartitionName'   => 'PT_GLOBAL_SVC',
                                                        'index'                => 3,
                                                        ],
                                                        [
                                                        'routePartitionName'   => 'PT_GLOBAL_XLATE',
                                                        'index'                => 4,
                                                        ],
                                                        [
                                                        'routePartitionName'   => 'PT_PSTN_LOCAL_10_DIGIT',
                                                        'index'                => 5,
                                                        ],
                                                        [
                                                        'routePartitionName'   => 'PT_PSTN_TOLLFREE',
                                                        'index'                => 6,
                                                        ],
                                                        [
                                                        'routePartitionName'   => 'PT_PSTN_LD',
                                                        'index'                => 7,
                                                        ],
                                                    ],
                                        ],
                    ],
                ];
        $result = [];
        // Check if the object already exists. If it isn't then add it.
        foreach ($DATA as $PARTITION) {
            if (! empty($objects)) {
                if (in_array($PARTITION['name'], $objects)) {
                    $result[$TYPE][] = "{$TYPE} Skipping... {$PARTITION['name']} already exists.";
                } else {
                    $result[$TYPE][] = $this->wrap_add_object($PARTITION, $TYPE);
                }
            } else {
                $result[$TYPE][] = $this->wrap_add_object($PARTITION, $TYPE);
            }
        }

        return $result;
    }

    public function addGlobalRoutePartitions()
    {
        $TYPE = 'RoutePattern';

        // Prepared datastructure
        $DATA = [

                    // Interntational
                    [
                        'pattern'                        => '9.011!',
                        'description'                    => 'NANP International Calling',
                        'routePartitionName'             => 'PT_PSTN_INTL',
                        'blockEnable'                    => 'false',
                        'useCallingPartyPhoneMask'       => 'Default',
                        'networkLocation'                => 'OffNet',
                        'patternUrgency'                 => 'false',

                        'destination'                    => [
                                                            'routeListName' => 'Universal-Route_List',

                                                        ],
                    ],
                    [
                        'pattern'                                  => '9.011!#',
                        'description'                              => 'NANP International Calling',
                        'routePartitionName'                       => 'PT_PSTN_INTL',
                        'blockEnable'                              => 'false',
                        'useCallingPartyPhoneMask'                 => 'Default',
                        'networkLocation'                          => 'OffNet',
                        'patternUrgency'                           => 'false',
                        'digitDiscardInstructionName'              => 'Trailing-#',

                        'destination'                    => [
                                                            'routeListName' => 'Universal-Route_List',

                                                        ],
                    ],

                    // Local 10 Digit Calling
                    [
                        'pattern'                        => '9.[2-9]XX[2-9]XXXXXX',
                        'description'                    => 'NANP Local 10 Digit Calling',
                        'routePartitionName'             => 'PT_PSTN_LOCAL_10_DIGIT',
                        'blockEnable'                    => 'false',
                        'useCallingPartyPhoneMask'       => 'Default',
                        'networkLocation'                => 'OffNet',
                        'patternUrgency'                 => 'false',

                        'destination'                    => [
                                                            'routeListName' => 'Universal-Route_List',

                                                        ],
                    ],

                    // Toll Free Calling
                    [
                        'pattern'                        => '9.1800[2-9]XXXXXX',
                        'description'                    => 'NANP Toll Free Calling',
                        'routePartitionName'             => 'PT_PSTN_TOLLFREE',
                        'blockEnable'                    => 'false',
                        'useCallingPartyPhoneMask'       => 'Default',
                        'networkLocation'                => 'OffNet',
                        'patternUrgency'                 => 'false',

                        'destination'                    => [
                                                            'routeListName' => 'Universal-Route_List',

                                                        ],
                    ],
                    [
                        'pattern'                        => '9.1888[2-9]XXXXXX',
                        'description'                    => 'NANP Toll Free Calling',
                        'routePartitionName'             => 'PT_PSTN_TOLLFREE',
                        'blockEnable'                    => 'false',
                        'useCallingPartyPhoneMask'       => 'Default',
                        'networkLocation'                => 'OffNet',
                        'patternUrgency'                 => 'false',

                        'destination'                    => [
                                                            'routeListName' => 'Universal-Route_List',

                                                        ],
                    ],
                    [
                        'pattern'                        => '9.1877[2-9]XXXXXX',
                        'description'                    => 'NANP Toll Free Calling',
                        'routePartitionName'             => 'PT_PSTN_TOLLFREE',
                        'blockEnable'                    => 'false',
                        'useCallingPartyPhoneMask'       => 'Default',
                        'networkLocation'                => 'OffNet',
                        'patternUrgency'                 => 'false',

                        'destination'                    => [
                                                            'routeListName' => 'Universal-Route_List',

                                                        ],
                    ],
                    [
                        'pattern'                        => '9.1866[2-9]XXXXXX',
                        'description'                    => 'NANP Toll Free Calling',
                        'routePartitionName'             => 'PT_PSTN_TOLLFREE',
                        'blockEnable'                    => 'false',
                        'useCallingPartyPhoneMask'       => 'Default',
                        'networkLocation'                => 'OffNet',
                        'patternUrgency'                 => 'false',

                        'destination'                    => [
                                                            'routeListName' => 'Universal-Route_List',

                                                        ],
                    ],
                    [
                        'pattern'                        => '9.1855[2-9]XXXXXX',
                        'description'                    => 'NANP Toll Free Calling',
                        'routePartitionName'             => 'PT_PSTN_TOLLFREE',
                        'blockEnable'                    => 'false',
                        'useCallingPartyPhoneMask'       => 'Default',
                        'networkLocation'                => 'OffNet',
                        'patternUrgency'                 => 'false',

                        'destination'                    => [
                                                            'routeListName' => 'Universal-Route_List',

                                                        ],
                    ],
                    [
                        'pattern'                        => '9.1844[2-9]XXXXXX',
                        'description'                    => 'NANP Toll Free Calling',
                        'routePartitionName'             => 'PT_PSTN_TOLLFREE',
                        'blockEnable'                    => 'false',
                        'useCallingPartyPhoneMask'       => 'Default',
                        'networkLocation'                => 'OffNet',
                        'patternUrgency'                 => 'false',

                        'destination'                    => [
                                                            'routeListName' => 'Universal-Route_List',

                                                        ],
                    ],
                    [
                        'pattern'                        => '9.1[2-9]XX[2-9]XXXXXX',
                        'description'                    => 'NANP Long Distance Calling',
                        'routePartitionName'             => 'PT_PSTN_LD',
                        'blockEnable'                    => 'false',
                        'useCallingPartyPhoneMask'       => 'Default',
                        'networkLocation'                => 'OffNet',
                        'patternUrgency'                 => 'false',

                        'destination'                    => [
                                                            'routeListName' => 'Universal-Route_List',

                                                        ],
                    ],

                    // E164 Calling
                    // E164 - International Calling
                    [
                        'pattern'                        => '\+!',
                        'description'                    => 'E164 International Calling',
                        'routePartitionName'             => 'PT_PSTN_INTL',
                        'blockEnable'                    => 'false',
                        'useCallingPartyPhoneMask'       => 'Default',
                        'networkLocation'                => 'OffNet',
                        'patternUrgency'                 => 'false',

                        'destination'                    => [
                                                            'routeListName' => 'Universal-Route_List',

                                                        ],
                    ],
                    [
                        'pattern'                        => '\+011',
                        'description'                    => 'E164 International Calling',
                        'routePartitionName'             => 'PT_PSTN_INTL',
                        'blockEnable'                    => 'false',
                        'useCallingPartyPhoneMask'       => 'Default',
                        'networkLocation'                => 'OffNet',
                        'patternUrgency'                 => 'false',

                        'destination'                    => [
                                                            'routeListName' => 'Universal-Route_List',

                                                        ],
                    ],

                    // E164 - Long Distance Calling
                    [
                        'pattern'                        => '\+1[2-9]XX[2-9]XXXXXX',
                        'description'                    => 'E164 Long Distance Calling',
                        'routePartitionName'             => 'PT_PSTN_LD',
                        'blockEnable'                    => 'false',
                        'useCallingPartyPhoneMask'       => 'Default',
                        'networkLocation'                => 'OffNet',
                        'patternUrgency'                 => 'false',

                        'destination'                    => [
                                                            'routeListName' => 'Universal-Route_List',

                                                        ],
                    ],

                    // E164 - Toll Free Calling
                    [
                        'pattern'                        => '\+1800[2-9]XXXXXX',
                        'description'                    => 'E164 Toll Free Calling',
                        'routePartitionName'             => 'PT_PSTN_TOLLFREE',
                        'blockEnable'                    => 'false',
                        'useCallingPartyPhoneMask'       => 'Default',
                        'networkLocation'                => 'OffNet',
                        'patternUrgency'                 => 'false',

                        'destination'                    => [
                                                            'routeListName' => 'Universal-Route_List',

                                                        ],
                    ],
                    [
                        'pattern'                        => '\+1888[2-9]XXXXXX',
                        'description'                    => 'E164 Toll Free Calling',
                        'routePartitionName'             => 'PT_PSTN_TOLLFREE',
                        'blockEnable'                    => 'false',
                        'useCallingPartyPhoneMask'       => 'Default',
                        'networkLocation'                => 'OffNet',
                        'patternUrgency'                 => 'false',

                        'destination'                    => [
                                                            'routeListName' => 'Universal-Route_List',

                                                        ],
                    ],
                    [
                        'pattern'                        => '\+1877[2-9]XXXXXX',
                        'description'                    => 'E164 Toll Free Calling',
                        'routePartitionName'             => 'PT_PSTN_TOLLFREE',
                        'blockEnable'                    => 'false',
                        'useCallingPartyPhoneMask'       => 'Default',
                        'networkLocation'                => 'OffNet',
                        'patternUrgency'                 => 'false',

                        'destination'                    => [
                                                            'routeListName' => 'Universal-Route_List',

                                                        ],
                    ],
                    [
                        'pattern'                        => '\+1866[2-9]XXXXXX',
                        'description'                    => 'E164 Toll Free Calling',
                        'routePartitionName'             => 'PT_PSTN_TOLLFREE',
                        'blockEnable'                    => 'false',
                        'useCallingPartyPhoneMask'       => 'Default',
                        'networkLocation'                => 'OffNet',
                        'patternUrgency'                 => 'false',

                        'destination'                    => [
                                                            'routeListName' => 'Universal-Route_List',

                                                        ],
                    ],
                    [
                        'pattern'                        => '\+1855[2-9]XXXXXX',
                        'description'                    => 'E164 Toll Free Calling',
                        'routePartitionName'             => 'PT_PSTN_TOLLFREE',
                        'blockEnable'                    => 'false',
                        'useCallingPartyPhoneMask'       => 'Default',
                        'networkLocation'                => 'OffNet',
                        'patternUrgency'                 => 'false',

                        'destination'                    => [
                                                            'routeListName' => 'Universal-Route_List',

                                                        ],
                    ],
                    [
                        'pattern'                        => '\+1844[2-9]XXXXXX',
                        'description'                    => 'E164 Toll Free Calling',
                        'routePartitionName'             => 'PT_PSTN_TOLLFREE',
                        'blockEnable'                    => 'false',
                        'useCallingPartyPhoneMask'       => 'Default',
                        'networkLocation'                => 'OffNet',
                        'patternUrgency'                 => 'false',

                        'destination'                    => [
                                                            'routeListName' => 'Universal-Route_List',

                                                        ],
                    ],

                ];

        // Check if the object already exists. If it isn't then add it.
        foreach ($DATA as $PATTERN) {
            // Get a list of all current objects by type to use to see what is exists now.
            try {
                $objects = $this->cucm->get_object_type_by_site($PATTERN['routePartitionName'], $TYPE);
            } catch (\Exception $E) {
                echo 'Exception Getting RoutePartitions from CUCM:'.
                      "{$E->getMessage()}".
                      "Stack trace:\n".
                      "{$E->getTraceAsString()}".
                      "Data sent:\n";
            }

            if (! empty($objects)) {
                if (in_array($PATTERN['pattern'], $objects)) {
                    $result[$TYPE][] = "{$TYPE} Skipping... {$PATTERN['pattern']} already exists.";
                } else {
                    $result[$TYPE][] = $this->wrap_add_object($PATTERN, $TYPE);
                }
            } else {
                $result[$TYPE][] = $this->wrap_add_object($PATTERN, $TYPE);
            }
        }

        return $result;
    }

    // Add translation Patterns with blocked patterns for each level.
    public function addBlockRoutePartitions()
    {

// Pasted in from Excel with following headers.
// Pattern	Partition	Description
        $INPUT = <<<END
9.1[2-9]XX[2-9]XXXXXX	PT_BLOCK_LD	global block LD
9.[2-9]XX[2-9]XXXXXX	PT_BLOCK_LOCAL	global block local
9.1800[2-9]XXXXXX	PT_BLOCK_TOLLFREE	global block toll free
9.1888[2-9]XXXXXX	PT_BLOCK_TOLLFREE	global block toll free
9.1877[2-9]XXXXXX	PT_BLOCK_TOLLFREE	global block toll free
9.1866[2-9]XXXXXX	PT_BLOCK_TOLLFREE	global block toll free
9.1855[2-9]XXXXXX	PT_BLOCK_TOLLFREE	global block toll free
9.1844[2-9]XXXXXX	PT_BLOCK_TOLLFREE	global block toll free
9.011!	PT_BLOCK_INTL	global block intl
9.011!#	PT_BLOCK_INTL	global block intl
\+!	PT_BLOCK_INTL	global block intl
\+011!	PT_BLOCK_INTL	global block intl
\+1[2-9]XX[2-9]XXXXXX	PT_BLOCK_LD	global block LD
\+1800[2-9]XXXXXX	PT_BLOCK_TOLLFREE	global block toll free
\+1888[2-9]XXXXXX	PT_BLOCK_TOLLFREE	global block toll free
\+1877[2-9]XXXXXX	PT_BLOCK_TOLLFREE	global block toll free
\+1866[2-9]XXXXXX	PT_BLOCK_TOLLFREE	global block toll free
\+1855[2-9]XXXXXX	PT_BLOCK_TOLLFREE	global block toll free
9.1900[2-9]XXXXXX	PT_BLOCK_FRAUD	optional, known toll charge or fraud
9.1[89]76[2-9]XXXXXX	PT_BLOCK_FRAUD	optional, known toll charge or fraud
9.1809[2-9]XXXXXX	PT_BLOCK_FRAUD	optional, known toll charge or fraud
9.1264[2-9]XXXXXX	PT_BLOCK_FRAUD	optional, known toll charge or fraud
9.1284[2-9]XXXXXX	PT_BLOCK_FRAUD	optional, known toll charge or fraud
9.1[34]73[2-9]XXXXXX	PT_BLOCK_FRAUD	optional, known toll charge or fraud
9.1597[2-9]XXXXXX	PT_BLOCK_FRAUD	optional, known toll charge or fraud
9.1664[2-9]XXXXXX	PT_BLOCK_FRAUD	optional, known toll charge or fraud
9.1767[2-9]XXXXXX	PT_BLOCK_FRAUD	optional, known toll charge or fraud
9.1852[2-9]XXXXXX	PT_BLOCK_FRAUD	optional, known toll charge or fraud
9.1869[2-9]XXXXXX	PT_BLOCK_FRAUD	optional, known toll charge or fraud
9.1340[2-9]XXXXXX	PT_BLOCK_FRAUD	optional, known toll charge or fraud
9.1671[2-9]XXXXXX	PT_BLOCK_FRAUD	optional, known toll charge or fraud
9.1784[2-9]XXXXXX	PT_BLOCK_FRAUD	optional, known toll charge or fraud
9.1268[2-9]XXXXXX	PT_BLOCK_FRAUD	optional, known toll charge or fraud
9.1345[2-9]XXXXXX	PT_BLOCK_FRAUD	optional, known toll charge or fraud
9.1441[2-9]XXXXXX	PT_BLOCK_FRAUD	optional, known toll charge or fraud
9.1592[2-9]XXXXXX	PT_BLOCK_FRAUD	optional, known toll charge or fraud
9.1649[2-9]XXXXXX	PT_BLOCK_FRAUD	optional, known toll charge or fraud
9.1758[2-9]XXXXXX	PT_BLOCK_FRAUD	optional, known toll charge or fraud
9.1787[2-9]XXXXXX	PT_BLOCK_FRAUD	optional, known toll charge or fraud
9.1868[2-9]XXXXXX	PT_BLOCK_FRAUD	optional, known toll charge or fraud
9.1242[2-9]XXXXXX	PT_BLOCK_FRAUD	optional, known toll charge or fraud
9.1246[2-9]XXXXXX	PT_BLOCK_FRAUD	optional, known toll charge or fraud
\+1900[2-9]XXXXXX	PT_BLOCK_FRAUD	optional, known toll charge or fraud
\+1[89]76[2-9]XXXXXX	PT_BLOCK_FRAUD	optional, known toll charge or fraud
\+1809[2-9]XXXXXX	PT_BLOCK_FRAUD	optional, known toll charge or fraud
\+1264[2-9]XXXXXX	PT_BLOCK_FRAUD	optional, known toll charge or fraud
\+1284[2-9]XXXXXX	PT_BLOCK_FRAUD	optional, known toll charge or fraud
\+1[34]73[2-9]XXXXXX	PT_BLOCK_FRAUD	optional, known toll charge or fraud
\+1597[2-9]XXXXXX	PT_BLOCK_FRAUD	optional, known toll charge or fraud
\+1664[2-9]XXXXXX	PT_BLOCK_FRAUD	optional, known toll charge or fraud
\+1767[2-9]XXXXXX	PT_BLOCK_FRAUD	optional, known toll charge or fraud
\+1852[2-9]XXXXXX	PT_BLOCK_FRAUD	optional, known toll charge or fraud
\+1869[2-9]XXXXXX	PT_BLOCK_FRAUD	optional, known toll charge or fraud
\+1340[2-9]XXXXXX	PT_BLOCK_FRAUD	optional, known toll charge or fraud
\+1671[2-9]XXXXXX	PT_BLOCK_FRAUD	optional, known toll charge or fraud
\+1784[2-9]XXXXXX	PT_BLOCK_FRAUD	optional, known toll charge or fraud
\+1268[2-9]XXXXXX	PT_BLOCK_FRAUD	optional, known toll charge or fraud
\+1345[2-9]XXXXXX	PT_BLOCK_FRAUD	optional, known toll charge or fraud
\+1441[2-9]XXXXXX	PT_BLOCK_FRAUD	optional, known toll charge or fraud
\+1592[2-9]XXXXXX	PT_BLOCK_FRAUD	optional, known toll charge or fraud
\+1649[2-9]XXXXXX	PT_BLOCK_FRAUD	optional, known toll charge or fraud
\+1758[2-9]XXXXXX	PT_BLOCK_FRAUD	optional, known toll charge or fraud
\+1787[2-9]XXXXXX	PT_BLOCK_FRAUD	optional, known toll charge or fraud
\+1868[2-9]XXXXXX	PT_BLOCK_FRAUD	optional, known toll charge or fraud
\+1242[2-9]XXXXXX	PT_BLOCK_FRAUD	optional, known toll charge or fraud
\+1246[2-9]XXXXXX	PT_BLOCK_FRAUD	optional, known toll charge or fraud
END;

        // Map the tab delimited string to array with mapped keys
        $ARRAY = array_map(
                            // Anonymous inline function callback
                            function ($LINE) {
                                // split each row into a tab delimited array
                                                return array_combine(
                                                                        // And map these keys to each value extracted
                                                                        [
                                                                            'pattern',    'routePartitionName',    'description',
                                                                        ], explode("\t", $LINE)
                                                                    );
                            }, explode("\n", $INPUT)
                        );

        // remove empty elements from the data set (they are not arrays)
        //$DATA = [];
        foreach ($ARRAY as $KEY => $ELEMENT) {
            if (! is_array($ELEMENT)) {
                unset($ARRAY[$KEY]);
            } else {
                // Add required fields to the array
                $ELEMENT['blockEnable'] = 'true';
                $ELEMENT['useCallingPartyPhoneMask'] = 'Default';
                $ELEMENT['networkLocation'] = 'OffNet';
                $ELEMENT['patternUrgency'] = 'false';
                $ELEMENT['usage'] = 'Translation';
                //print_r($ELEMENT);
                $DATA[] = $ELEMENT;
            }
        }

        //print_r($DATA);

        /* Prepared datastructure
        $DATA = [

                    // Interntational
                    [
                        'pattern'                        => '9.011!',
                        'description'                    => 'NANP International Calling',
                        'routePartitionName'             => 'PT_PSTN_INTL',
                        'blockEnable'                    => 'false',
                        'useCallingPartyPhoneMask'       => 'Default',
                        'networkLocation'                => 'OffNet',
                        'patternUrgency'                 => 'false',

                        'destination'                    => [
                                                            'routeListName' => 'Universal-Route_List',

                                                        ],
                    ],
                ];

        */

        $TYPE = 'TransPattern';

        // Check if the object already exists. If it isn't then add it.
        foreach ($DATA as $PATTERN) {
            // Get a list of all current objects by type to use to see what is exists now.
            try {
                $objects = $this->cucm->get_object_type_by_site($PATTERN['routePartitionName'], $TYPE);
            } catch (\Exception $E) {
                echo 'Exception Getting TransPatterns from CUCM:'.
                      "{$E->getMessage()}".
                      "Stack trace:\n".
                      "{$E->getTraceAsString()}".
                      "Data sent:\n";
            }

            if (! empty($objects)) {
                if (in_array($PATTERN['pattern'], $objects)) {
                    $result[$TYPE][] = "{$TYPE} Skipping... {$PATTERN['pattern']} already exists.";
                } else {
                    $result[$TYPE][] = $this->wrap_add_object($PATTERN, $TYPE);
                }
            } else {
                $result[$TYPE][] = $this->wrap_add_object($PATTERN, $TYPE);
            }
        }

        return $result;
    }

    // Add Application Dial Rules
    public function addApplicationDialRules()
    {
        $DATA = [

                    // 10digit-to-E164
                    [
                        'name'                                 => '10digit-to-E164',
                        'description'                          => 'take 10 digits map to E164',
                        'numberBeginWith'                      => '',
                        'numberOfDigits'                       => '10',
                        'digitsToBeRemoved'                    => '0',
                        'prefixPattern'                        => '+1',
                        'priority'                             => '0',
                    ],
                    // 11digit-to-E164
                    [
                        'name'                                 => '11digit-to-E164',
                        'description'                          => '11 digits beginning w/ 1 to E164',
                        'numberBeginWith'                      => '1',
                        'numberOfDigits'                       => '11',
                        'digitsToBeRemoved'                    => '0',
                        'prefixPattern'                        => '+',
                        'priority'                             => '1',
                    ],
                ];

        $TYPE = 'ApplicationDialRules';

        foreach ($DATA as $RULE) {
            // Get a list of all current objects by type to use to see what is exists now.
            try {
                $objects = $this->cucm->get_object_type_by_site($RULE['name'], $TYPE);
            } catch (\Exception $E) {
                echo 'Exception Getting Application Dial Rules from CUCM:'.
                      "{$E->getMessage()}".
                      "Stack trace:\n".
                      "{$E->getTraceAsString()}".
                      "Data sent:\n";
            }

            if (! empty($objects)) {
                if (in_array($RULE['name'], $objects)) {
                    $result[$TYPE][] = "{$TYPE} Skipping... {$RULE['name']} already exists.";
                } else {
                    $result[$TYPE][] = $this->wrap_add_object($RULE, $TYPE);
                }
            } else {
                $result[$TYPE][] = $this->wrap_add_object($RULE, $TYPE);
            }
        }

        return $result;
    }

    // Add Calling Party Transformations
    public function addCallingPartyTransformationPatterns()
    {
        $DATA = [
                    [
                        'pattern'                        => '\+.[2-9]XXXXXXXXX',
                        //'description'                    => 'at the gw/trunk the digits received from the carrier are prefixed w/ a +, this is simply to remap into E164',
                        'description'                    => 'GW/Trunk add +, this is simply to remap into E164',
                        'routePartitionName'             => 'PT_GLOBAL_GW_INCOMING_CALLING_XFORM',
                        'digitDiscardInstructionName'    => 'predot',
                        'callingPartyPrefixDigits'       => '+1',
                    ],
                    [
                        'pattern'                        => '\+011.!',
                        //'description'                    => 'at the gw/trunk the digits received from the carrier are prefixed w/ a +, this is simply to remap into E164',
                        'description'                    => 'GW/Trunk add +, this is simply to remap into E164',
                        'routePartitionName'             => 'PT_GLOBAL_GW_INCOMING_CALLING_XFORM',
                        'digitDiscardInstructionName'    => 'predot',
                        'callingPartyPrefixDigits'       => '+',
                    ],
                    [
                        'pattern'                        => '\+1.[2-9]XXXXXXXXX',
                        //'description'                    => 'since weve set all inbound calls to show a e164 in caller id its now important that when we send that back out to the carrier for things like call forward or SNR we dont send them and e164 calling party number so this remaps the call into the original format',
                        'description'                    => 'Toward Carrier - convert to original format',
                        'routePartitionName'             => 'PT_GLOBAL_GW_OUTGOING_CALLING_XFORM',
                        'digitDiscardInstructionName'    => 'predot',
                        'callingPartyPrefixDigits'       => '',
                    ],
                    [
                        'pattern'                        => '\+.!',
                        //'description'                    => 'since weve set all inbound calls to show a e164 in caller id its now important that when we send that back out to the carrier for things like call forward or SNR we dont send them and e164 calling party number so this remaps the call into the original format',
                        'description'                    => 'Toward Carrier - convert to original format',
                        'routePartitionName'             => 'PT_GLOBAL_GW_OUTGOING_CALLING_XFORM',
                        'digitDiscardInstructionName'    => 'predot',
                        'callingPartyPrefixDigits'       => '011',
                    ],
                ];

        $TYPE = 'CallingPartyTransformationPattern';

        foreach ($DATA as $OBJECT) {
            // Get a list of all current objects by type to use to see what is exists now.
            try {
                $objects = $this->cucm->get_object_type_by_site($OBJECT['routePartitionName'], $TYPE);
            } catch (\Exception $E) {
                echo 'Exception Getting CallingPartyTransformationPattern from CUCM:'.
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

        return $result;
    }

    // Add Calling Party Transformations
    public function addCalledPartyTransformationPatterns()
    {
        $DATA = [
                    [
                        'pattern'                           => '\+.!',
                        'description'                       => 'digits sent to gw or session boarder controller',
                        'routePartitionName'                => 'PT_GLOBAL_GW_CALLED_XFORM',
                        'digitDiscardInstructionName'       => 'predot',
                        'calledPartyPrefixDigits'           => '9011',
                    ],
                    [
                        'pattern'                           => '\+.011!',
                        'description'                       => 'digits sent to gw or session boarder controller',
                        'routePartitionName'                => 'PT_GLOBAL_GW_CALLED_XFORM',
                        'digitDiscardInstructionName'       => 'predot',
                        'calledPartyPrefixDigits'           => '9',
                    ],
                    [
                        'pattern'                           => '\+1.[2-9]XX[2-9]XXXXXX',
                        'description'                       => 'digits sent to gw or session boarder controller',
                        'routePartitionName'                => 'PT_GLOBAL_GW_CALLED_XFORM',
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

        return $result;
    }
}
