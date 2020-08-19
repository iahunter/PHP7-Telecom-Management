<?php

namespace App\Console\Commands\CallManager;

use Illuminate\Console\Command;

class SiteLocal911 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'callmanager:sitelocal911';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'DO NOT RUN!!! Custom Script - Builds All Site 911 Local Partiions, Route Lists, Route Patterns, and Clear out old 911! and 9911! patterns from Site Partitions ';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        // Construct new cucm object
        $this->cucm = new \Iahunter\CallmanagerAXL\Callmanager(env('CALLMANAGER_URL'),
                                                    storage_path(env('CALLMANAGER_WSDL')),
                                                    env('CALLMANAGER_USER'),
                                                    env('CALLMANAGER_PASS')
                                                    );

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $DEV = true;                                        // Toggle Development
        $DEBUG = true;
        $COUNT = 0;

        // Step 1. Get a list of sites by getting All the Device Pools.
        $sites = $this->getSites();                            // Get a list of sites by calling get device pools and discard ones we don't care about.
        $sites = [];                                        // Comment this out to actually run this.
        foreach ($sites as $site) {

            // Step 2. Get everything to do with the site for each site.
            $site_details = $this->getSiteDetails($site);

            // Step 3. Build the Array of New Partitions to be added for each site.
            $newpartitions = $this->build_new_partitions_for_site($site);

            if ($DEBUG) {
                print_r($newpartitions);
            }

            foreach ($newpartitions as $partition) {
                //print_r($partition);
                //print "Partitions: ";
                $partitions = $site_details['RoutePartition'];
                //print_r($partitions);

                if (! in_array($partition['name'], $site_details['RoutePartition'])) {
                    echo $partition['name'].'Does not exist so we are going to add it.';

                    // Add Partion
                    try {
                        echo "Building Partition {$partition['name']}...".PHP_EOL;
                        $TYPE = 'RoutePartition';
                        $partitions = $this->cucm->add_object_type_by_assoc($partition, $TYPE);
                        print_r($partitions);
                    } catch (\Exception $e) {
                        echo 'Callmanager blew uP: '.$e->getMessage().PHP_EOL;
                        dd($e->getTrace());
                    }
                } else {
                    echo "Skipping {$partition['name']}... It already exists";
                    //continue;
                }
            }

            // Step 4. Update current CSS with new Partition at the end of the list for each site.

            $current_css = $site_details['Css'];

            foreach ($current_css as $key => $value) {
                echo "Updating {$value}...".PHP_EOL;

                // Get the current CSS Members to determine the Max Index number so we know what to add the new partition to at the end of the list.
                echo "Getting CSS Details for {$value} from CUCM...".PHP_EOL;
                try {
                    $UUID = $key;
                    echo 'UUID: '.$UUID;

                    $css_details = $this->cucm->get_object_type_by_uuid($UUID, 'Css');        // Get the current details of each CSS to get the current partition memebers.

                    print_r($css_details);
                } catch (\Exception $e) {
                    echo 'Callmanager blew uP: '.$e->getMessage().PHP_EOL;
                    dd($e->getTrace());
                }
                echo 'Getting CSS Members...';
                print_r($css_details);

                if ($css_details['partitionUsage'] == 'Intercom') {
                    echo "Skipping CSS {$value}... We don't want to act on Intercom CSSs";
                    print_r($css_details);
                    continue;
                }

                $css_report = $this->getCssMembersbyCSS($css_details);                    // Get CSS Partition Members - Returns array key by index.

                // Check if the array comes back is empty - If it is then move on to the next CSS.
                if (empty($css_report)) {
                    echo "Skipping CSS {$value}... CSS Report empty...";
                    print_r($css_details);
                    continue;
                }

                $css_next_index = max(array_keys($css_report[$value])) + 1;                // Set the next index ID by adding one to the max index number that currently exists.
                //echo 'HREE';

                foreach ($newpartitions as $partition) {
                    $MATCH = false;                                                        // Set Match back to false for each.
                    //print_r($partition);
                    foreach ($css_report as $css => $members) {
                        //print_r($members);
                        foreach ($members as $member) {
                            if ($member['name'] == $partition['name']) {                    // Check to see if new partition name matches any existing partition members.
                                $MATCH = true;
                                //print "Match Found!!! Setting Match to true".PHP_EOL;
                            }
                        }
                    }
                    echo 'Match = '.$MATCH.PHP_EOL;
                    if ($MATCH == true) {
                        echo "Skipping {$partition['name']} it already exists".PHP_EOL;
                        continue;
                    }

                    $updated_css_array = $this->add_partition_to_end_of_css_array($value, $partition['name'], $css_next_index);            // Loop thru the new partitions array and append the partition to the CSS in CUCM.

                    echo "Updating {$value} in CUCM...".PHP_EOL;
                    try {
                        $UUID = $key;
                        //print_r($updated_css_array);
                        echo 'Updating UUID: '.$UUID.PHP_EOL;
                        $update_css = $this->cucm->update_object_type_by_assoc($updated_css_array, 'Css');                // Now update CSS in CUCM.

                        echo "Updated {$value} in CUCM Successfully!!! | ".$update_css.PHP_EOL;
                    } catch (\Exception $e) {
                        echo 'Callmanager blew uP: '.$e->getMessage().PHP_EOL;
                        dd($e->getTrace());
                    }
                }
            }

            // Step 5. Create new 911 Route List and add the SLRG go it as a member for each site.

            /* You can get a RouteList Report by uncommenting this.
            if(!empty($site_details['RouteList'])){
                //print $site.": RouteGroup is not empty!!!".PHP_EOL;
                //print_r($site_details['RouteList']);
                //print PHP_EOL;

                foreach($site_details['RouteList'] as $UUID => $value){
                    $RL = $this->cucm->get_object_type_by_uuid($UUID, 'RouteList');
                    $RLNAME = $RL['name'];
                    if(!empty($RG = $RL['members'])){
                        $RG = $RL['members']['member']['routeGroupName']['_'];
                        print $RLNAME." | ".$RG.PHP_EOL;
                    }else{
                        print $RLNAME." | has no members assigned...".PHP_EOL;
                    }

                }
            }
            */

            $DPUUID = $site_details['DevicePool'];

            foreach ($DPUUID as $key => $value) {
                $UUID = $key;
                $DP = $this->cucm->get_object_type_by_uuid($UUID, 'DevicePool');            // Get Device Pool Details
                //print_r($DP);
                $SLRG = $DP['localRouteGroup']['value'];                                    // Get the SLRG
                $CCMGRP = $DP['callManagerGroupName']['_'];                                    // Get the Call Manager Group
            }

            $RL911 = $this->build_new_911_routelist_array($site, $CCMGRP, $SLRG);            // Create the array to pass to the add function.

            //foreach($site_details['RouteList'] as $key => $value){
            if (in_array($RL911['name'], $site_details['RouteList'])) {
                echo "Skipping {$RL911['name']}... Already exists...";
            } else {
                echo "Adding {$RL911['name']} to CUCM...".PHP_EOL;
                // Add Route List to CUCM
                try {
                    print_r($RL911);
                    echo "Building RouteList {$RL911['name']}...".PHP_EOL;
                    $TYPE = 'RouteList';
                    $routelist = $this->cucm->add_object_type_by_assoc($RL911, $TYPE);
                    print_r($routelist);
                    echo "RouteList {$RL911['name']} added succesfully | {$routelist}".PHP_EOL;
                } catch (\Exception $e) {
                    echo 'Callmanager blew uP: '.$e->getMessage().PHP_EOL;
                    dd($e->getTrace());
                }
            }

            // Step 6. Add Route Patterns with Partition and RL assignements for each site.

            print_r($site_details);
            $currentroutes = $site_details['RoutePattern'];

            print_r($currentroutes);

            $e911routes = $this->build_new_911_routepatterns_array($site);

            foreach ($e911routes as $e911route) {
                if (in_array($e911route['pattern'], $site_details['RoutePattern'])) {
                    echo "Skipping {$e911route['pattern']}... Already exists...";
                } else {
                    echo "Adding Pattern: {$e911route['pattern']} with {$e911route['routePartitionName']} to CUCM...".PHP_EOL;
                    // Add Route Pattern to CUCM
                    try {
                        print_r($e911routes);
                        echo "Building RoutePattern {$e911route['pattern']}...".PHP_EOL;
                        $TYPE = 'RoutePattern';
                        $routepattern = $this->cucm->add_object_type_by_assoc($e911route, $TYPE);
                        //print_r($routepattern);
                        echo "RoutePattern {$e911route['pattern']} with {$e911route['routePartitionName']} added succesfully | {$routepattern}".PHP_EOL;
                    } catch (\Exception $e) {
                        echo 'Callmanager blew uP: '.$e->getMessage().PHP_EOL;
                        dd($e->getTrace());
                    }
                }
            }
            /*
            I tried to get this to print out the differences betweent the start and finish but not working.

            $changes = [];
            // Get the stie details after the changes.
            $site_details_after = $this->getSiteDetails($site);

            //print_r($site_details);
            //print_r($site_details_after);

             Do a compare of the start and end of the site array after the changes.
            foreach($site_details as $key => $value){
                //print_r($value);
                $changes = array_diff_assoc($value, $site_details_after[$key]);
            }
            print_r($changes);
            */

            if ($DEV == true) {
                while ($COUNT > 1) {
                    echo 'Count:'.$COUNT.PHP_EOL;
                    echo "Killing process... In Development Mode... Only executing {$COUNT} Sites".PHP_EOL;
                    exit();
                }
            }

            echo 'Count:'.$COUNT++.PHP_EOL;
        }
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
    protected function getSiteDetails($SITE)
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
                        print_r($partition);
                        $MEMBER = [];

                        if (isset($partition['routePartitionName'])) {
                            $MEMBER['name'] = $partition['routePartitionName']['_'];
                            $MEMBER['index'] = $partition['index'];

                            echo $partition['routePartitionName']['_'];
                        } else {
                            return $RESULTS;
                        }

                        // Append Member to Members with the key as the index number.
                        $MEMBERS[$MEMBER['index']] = $MEMBER;
                    }
                }
            }

            // Append CSS Members to Results with Name as Key.
            print_r($MEMBERS);
            if (! empty($MEMBERS)) {
                $RESULTS[$css['name']] = $MEMBERS;
            }
        }

        return $RESULTS;
    }

    protected function compare_changes($array1, $array2)
    {
        $RESULTS = [];

        // Do a compare of the start and end of the site array after the changes.
        foreach ($array1 as $key => $value) {
            print_r($value);

            $RESULTS[$key] = array_diff_assoc($value, $site_details_after[$key]);
        }
        print_r($RESULTS);

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
                'selectionOrder'                                                    => 1,
            ],
            ],

        ];

        echo "Building Site 911 Route List with {$SLRG} in CUCM...".PHP_EOL;
        try {
            // Add Partion
            $partitions = $this->cucm->add_object_type_by_assoc($DATA, $TYPE);
            print_r($partitions);

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
        echo 'Building Site partitions Array...'.PHP_EOL;

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

    // Add Route Patterns
    protected function add_partition_to_end_of_css_array($CSS, $PARTITION, $CSS_NEXT_INDEX)
    {
        echo 'Building Site partitions Array...'.PHP_EOL;

        // Build Array of CSS adding new Partition with index of 15.
        $DATA = [
            'name'                => $CSS,
            'addMembers'          => [
                'member' => [
                    'routePartitionName'       => $PARTITION,
                    'index'                    => $CSS_NEXT_INDEX,
                ],
            ],
        ];

        return $DATA;
    }

    // Add 911 Route List
    protected function build_new_911_routelist_array($SITE, $CCMGRP, $SLRG)
    {
        echo "Building {$SITE} 911 Route List Array...".PHP_EOL;

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
        echo 'Building Site 911 Route Patterns Array...'.PHP_EOL;

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
