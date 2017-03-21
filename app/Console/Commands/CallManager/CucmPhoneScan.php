<?php

namespace App\Console\Commands\CallManager;

use DB;
use Carbon\Carbon;
use App\Cucmphoneconfigs;
use Illuminate\Console\Command;

class CucmPhoneScan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'callmanager:phonescan';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan CUCM Phones and Write to Database';

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

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $start = Carbon::now();
        echo 'Starting Site Scan at: '.$start.PHP_EOL;
        // Step 1. Get a list of sites by getting All the Device Pools.
        $sites = $this->getSites();                                    // Get a list of sites by calling get device pools and discard ones we don't care about.
        //$sites = ['TRAVIS01'];
        $sitetotalcount = count($sites);
        $sitecount = 0;
        foreach ($sites as $site) {
            $sitecount = $sitecount + 1;
            echo 'Getting Site: '.$site.' # '.$sitecount.' of '.$sitetotalcount.PHP_EOL;
            echo 'Start Time: '.$start.PHP_EOL;
            echo 'Current Time: '.Carbon::now().PHP_EOL;

            // Step 2. Get everything to do with the site for each site.
            $phonenames = $this->getPhonesNamesbySite($site);

            echo 'Found '.count($phonenames).' Phones in '.$site.PHP_EOL;
            $phonecount = 0;
            foreach ($phonenames as $key => $phonename) {
                //print $phonename.PHP_EOL;
                $phonedetails = $this->getphone($phonename);

                $phone['name'] = $phonename;
                $phone['config'] = $phonedetails;
                // Set string values for phone db
                $phone['devicepool'] = $phonedetails['devicePoolName']['_'];
                $phone['css'] = $devicepool = $phonedetails['callingSearchSpaceName']['_'];
                $phone['model'] = $devicepool = $phonedetails['model'];
                $phone['description'] = $devicepool = $phonedetails['description'];
                $phone['ownerid'] = $phonedetails['ownerUserName']['_'];

                // Get the Line details
                $phone['lines'] = $this->get_lines_details_by_phone_name($phonename);
                echo $phonecount = $phonecount + 1 .' of '.count($phonenames).' ';
                $this->create_update_phone($phone);
                //die();
            }
            echo PHP_EOL;
        }

        /***************************************
            Add DB Cleanup Actions HERE
        ****************************************/

        $end = Carbon::now();
        echo PHP_EOL;
        echo 'Start Time: '.$start.PHP_EOL;
        echo 'End Time: '.$end.PHP_EOL;
    }

    // Get a list of Sites by device pools.
    protected function getPhonesNamesbySite($site)
    {
        // $site = 'TRAVIS01';
        //echo 'Getting phones from CUCM Site:'.$site.'...'.PHP_EOL;
        try {
            $phones = $this->cucm->get_object_type_by_site($site, 'Phone');

            if (! $phones) {
                // Return blank array if no results in $didinfo.
                //echo 'No Phones Found!';

                return $phones;
            }

            return $phones;
        } catch (\Exception $e) {
            echo 'Callmanager blew uP: '.$e->getMessage().PHP_EOL;
            dd($e->getTrace());
        }
    }

    // Get a list of Sites by device pools.
    protected function get_lines_details_by_phone_name($NAME)
    {
        // $site = 'TRAVIS01';
        //echo 'Getting phone Lines from CUCM Phone:'.$NAME.'...'.PHP_EOL;
        try {
            $lines = $this->cucm->get_lines_details_by_phone_name($NAME);

            if (! $lines) {
                // Return blank array if no results in $didinfo.
                //echo 'No Lines Found!';

                return $lines;
            }

            return $lines;
        } catch (\Exception $e) {
            echo 'Callmanager blew uP: '.$e->getMessage().PHP_EOL;
            dd($e->getTrace());
        }
    }

    // Get a list of Sites by device pools.
    protected function getphone($NAME)
    {
        // $site = 'TRAVIS01';
        //echo 'Getting phone Lines from CUCM Phone:'.$NAME.'...'.PHP_EOL;
        try {
            $phone = $this->cucm->get_object_type_by_name($NAME, 'Phone');

            if (! $phone) {
                // Return blank array if no results in $didinfo.
                echo 'No Phone Found!';

                return $phone;
            }

            return $phone;
        } catch (\Exception $e) {
            echo 'Callmanager blew uP: '.$e->getMessage().PHP_EOL;
            dd($e->getTrace());
        }
    }

    //**************************************************************************//
    protected function getphonesfromdb()
    {
        $dbphones = DB::table('cucmphone')->where('deleted_at', '=', null)->select('name')->orderBy('devicepool')->get();
        $dbphones = json_decode(json_encode($dbsites), true);
        //print_r($dbsites);
        $phones = [];
        foreach ($dbphones as $phone) {
            $phones[] = $phone['name'];
        }

        return $phones;
    }

    protected function deletephone($name)
    {
        //echo 'ENTERED deletephone function';
        $record = Cucmsiteconfigs::where('sitecode', $name)->first();
        //print_r($record);
        return $record->delete();                                                            // Delete the did block.
    }

    // This updates DID records with new information AND clears out no longer used phone numbers / sets them to available
    protected function create_update_phone($newphone)
    {
        // Check if Site exists in the database
        if (Cucmphoneconfigs::where([['name', $newphone['name']]])->count()) {
            $phone = Cucmphoneconfigs::where([['name', $newphone['name']]])->first();

            //echo 'Phone Exists'.PHP_EOL;

            // Update Phone Record if exists with latest config.
            $phone->config = $newphone['config'];
            $phone->devicepool = $newphone['devicepool'];
            $phone->css = $devicepool = $newphone['css'];
            $phone->model = $devicepool = $newphone['model'];
            $phone->description = $devicepool = $newphone['description'];
            $phone->ownerid = $newphone['ownerid'];

            // Get the Line details
            $phone->lines = $newphone['lines'];

            //echo 'Saving Site with current config...'.PHP_EOL;
            $phone->save();
            echo 'Saved '.$newphone['name'].PHP_EOL;
        } else {
            // Create Phone
            //echo 'Creating Phone: '.$newphone['name'].PHP_EOL;
            Cucmphoneconfigs::create($newphone);
            echo 'Created Phone: '.$newphone['name'].PHP_EOL;
        }
    }

    // Get a list of Sites by device pools.
    protected function getSites()
    {
        echo 'Getting sites from CUCM...'.PHP_EOL;
        try {
            $sites = $this->cucm->get_site_names();
            //$sites = ["TRAVIS01"];

            // Array of DP we don't want to include.
            $discard = ['TEST', 'Self_Provisioning', 'ECD', '911Enable', 'ATT_SIP', 'Travis', 'CENCOLIT', 'TEMPLATE', 'CENTRAL_SBC_SIPTRUNKS'];

            if (! $sites) {
                // Return blank array if no results in $didinfo.
                echo 'sites is blank!';

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
}
