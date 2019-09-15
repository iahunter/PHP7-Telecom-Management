<?php

namespace App\Console\Commands\CallManager;

use DB;
use App\Did;
use App\Didblock;
use Illuminate\Console\Command;

class DidScanCucm extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'callmanager:didscan';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan configured DIDs in Callmanager';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     *
     * This function is what is kicked off by the console command.
     */
    public function handle()
    {

        // Get our list of NPA/NXX's
        $prefixes = $this->getDidNPANXXList();

        $possible_deletes = [];
        // Loop through our NPA/NXX's and get their devices out of call wrangler
        foreach ($prefixes as $npanxx) {
            $didinfo = [];
            try {
                // Get the devices for this npa/nxx out of cucm
                $didinfo = $this->getDidsByNPANXX($npanxx);
            } catch (\Exception $e) {
                echo 'Callmanager blew uP: '.$e->getMessage().PHP_EOL;
                dd($e->getTrace());
            }
            // Update all our DID information for this NPANXX based on those device records.
            $possible_deletes[$npanxx] = $this->updateDidInfo($npanxx, $didinfo);
        }

        // This will remove lines that are now available from the Line Cleanup Report.
        echo 'Starting Cleanup Quick Scan'.PHP_EOL;
        $this->updateNumberCleanupReport();
        echo 'Completed Cleanup Quick Scan'.PHP_EOL;
    }

    // This gets a SIMPLE array of NPA/NXX for our numbers in the database.
    protected function getDidNPANXXList()
    {
        //$prefixes = Didblock::select(DB::raw('substring(start,1,6) as npanxx'))->groupBy(DB::raw('npanxx'))->get();
        $results = Didblock::select(DB::raw('substring(start,1,6) as npanxx'))->distinct()->get();
        $prefixes = [];
        foreach ($results as $result) {
            $prefixes[] = $result->npanxx;
        }

        return $prefixes;
    }

    // Get the DID information for a single NPA/NXX and return a USEFUL array? key=>value by DID?
    protected function cleanup_number($uuid, $number)
    {
        $call_fowarded_numbers = [];

        //echo 'Getting Number: '.$number.' from CUCM...'.PHP_EOL;
        try {
            $cucm = new \Iahunter\CallmanagerAXL\Callmanager(env('CALLMANAGER_URL'),
                                                    storage_path(env('CALLMANAGER_WSDL')),
                                                    env('CALLMANAGER_USER'),
                                                    env('CALLMANAGER_PASS')
                                                    );

            $line = $cucm->get_object_type_by_uuid($uuid, 'Line');

            if ($line) {
                if (! $line['callForwardAll']['destination']) {
                    echo $number.' | '.$line['description'].' | '.$line['alertingName'].' | can be deleted!!!!'.PHP_EOL;

                    // Check if line has voicemail box built.
                        // If so do not delete or make available.

                    // Add Logic to go remove this Line from CUCM here....
                    // Add Logic to make number available here.
                }
                if ($line['callForwardAll']['destination']) {
                    echo "{$number} has Call Forwarding Set!!!! DO NOT DELETE... {$line['callForwardAll']['destination']}".PHP_EOL;

                    // return lines that have this set
                    return $line;
                }
            } else {
                throw new \Exception('No Line Found!!!');
            }

            unset($cucm);
        } catch (\Exception $e) {
            echo 'Callmanager blew uP: '.$e->getMessage().PHP_EOL;
            dd($e->getTrace());
        }
    }

    // Get the DID information for a single NPA/NXX and return a USEFUL array? key=>value by DID?
    protected function getDidsByNPANXX($npanxx)
    {
        echo 'Getting NAPNXX: '.$npanxx.' numbers from CUCM...'.PHP_EOL;
        try {
            $cucm = new \Iahunter\CallmanagerAXL\Callmanager(env('CALLMANAGER_URL'),
                                                    storage_path(env('CALLMANAGER_WSDL')),
                                                    env('CALLMANAGER_USER'),
                                                    env('CALLMANAGER_PASS')
                                                    );
            $didinfo = $cucm->get_route_plan_by_name($npanxx.'%');
            unset($cucm);
            // Process the junk we got back from call mangler and turn it into something useful
            $results = [];
            if (! $didinfo) {
                // Return blank array if no results in $didinfo.
                echo 'didinfo is blank!';

                return $results;
            }
            foreach ($didinfo as $idfk) {
                if (! isset($results[$idfk['dnOrPattern']])) {
                    $results[$idfk['dnOrPattern']] = [];
                }
                $results[$idfk['dnOrPattern']][] = $idfk;
            }
            if (! count($results)) {
                throw new \Exception('Indexed results from call mangler are empty!!');
            }

            return $results;
        } catch (\Exception $e) {
            echo 'Callmanager blew uP: '.$e->getMessage().PHP_EOL;
            dd($e->getTrace());
        }
    }

    // This updates DID records with new information AND clears out no longer used phone numbers / sets them to available
    protected function updateDidInfo($npanxx, $didinfo)
    {

        // Return array of the numbers that we need to look into cleaning up.
        $possible_deletes = [];

        // Get the DID records matching $npanxx.'%' - Only Valid for NANP Numbers
        if (\App\Did::where([['number', 'like', $npanxx.'%']])->count()) {
            //$dids = \App\Did::where([['country_code', '=', 1], ['number', 'like', $npanxx.'%']])->get();
            // Removed country code from the search to fix Mexico numbers - 010219 - TR
            $dids = \App\Did::where([['country_code', '=', 1], ['number', 'like', $npanxx.'%']])
                    ->orWhere([['country_code', '=', 52], ['number', 'like', $npanxx.'%']])
                    ->get();
        }

        // Go through all the mathcing DID's and update them, OR set them to available
        // maybe WRAP this in a try/catch block to handle individual number update failures!

        foreach ($dids as $did) {
            try {
                // Skip over excluded numbers. These may not be part of our block.
                if ($did->status == 'exclude') {
                    continue;
                }
                // SKIP updating OR making available DID's that are RESERVED!
                if ($did->status == 'reserved') {

                    // If its now built in the system, mark it as inuse.
                    if (isset($didinfo[$did->number])) {
                        $did->assignments = $didinfo[$did->number];
                        $did->status = 'inuse';
                        $did->system_id = 'CUCM-Enterprise-Cluster';
                    } else {
                        // If not skip it and leave it as reserved.
                        continue;
                    }
                }

                // IF this DID IS in the results from call wrangler, update it!
                if (isset($didinfo[$did->number])) {

                    // Check if this number has any assigned devices... Need to move this functionality to its own command and schedule.
                    foreach ($didinfo[$did->number] as $entry) {
                        if (isset($entry['routeDetail']) && ! $entry['routeDetail']) {
                            //print "{$entry['dnOrPattern']} - This number needs looked at!!!".PHP_EOL;
                            $possible_deletes[$entry['uuid']] = $entry['dnOrPattern'];
                        }
                    }
                    //print_r($didinfo[$did->number]);
                    $did->assignments = $didinfo[$did->number];
                    $did->status = 'inuse';
                    $did->system_id = 'CUCM-Enterprise-Cluster';
                // OTHERWISE if the number is NOT in the CUCM results, set it as AVAILABLE
                } else {
                    $did->assignments = null;
                    $did->status = 'available';
                    $did->system_id = null;
                    $did->mailbox = null;
                }
                //dd($did);
                $did->save();
            } catch (\Exception $e) {
                echo 'Exception processing one DID '.$did->number.' '.$e->getMessage().PHP_EOL;
            }
            echo 'Processing DID: '.$did->number.' '.PHP_EOL;
            //die();
        }

        return $possible_deletes;
    }

    protected function updateNumberCleanupReport()
    {

        // This report can be added to the hourly scan to update the json for the Cleanup Report. This is to remove lines that have been deleted from the report.
		$location = 'cucm/linecleanup/report.json'; 
		
        if (! file_exists(storage_path('cucm/linecleanup/report.json')) || ! is_readable(storage_path('cucm/linecleanup/report.json'))) {
            return 'FILE IS NOT BEING LOADED FROM: '.$location;
        }

        // Get the json from the file in storage.
        $json = file_get_contents(storage_path('cucm/linecleanup/report.json'));

        $data = json_decode($json, true);

        $data = (array) $data;

        //print_r($data);
        foreach ($data as $reportname => $numbers) {
            $report_array = [];
            if (is_array($numbers) && count($numbers)) {
                foreach ($numbers as $number) {
                    $numberusage = Did::where([['number', '=', $number['pattern']], ['country_code', '=', '1']])->first();

                    $numberusage = json_decode(json_encode($numberusage), true);

                    //print_r($numberusage);
                    //return $numberusage;
                    if ($numberusage['status'] == 'inuse') {
                        continue;
                    //unset($numbers[$number['uuid']]);
                        //return $number['pattern'];
                    } else {
                        unset($numbers[$number['uuid']]);
                        echo "Removed {$number['pattern']} from Number Cleanup Report".PHP_EOL;
                    }
                }
            }

            $data[$reportname] = $numbers;
        }

        // Save Site Config as JSON and upload to subversion for change tracking.
        $data = json_encode($data, JSON_PRETTY_PRINT);

        echo 'Saving output json to file...'.PHP_EOL;

        file_put_contents(storage_path('cucm/linecleanup/report.json'), $data);

        echo 'Saved to file...'.PHP_EOL;
    }
}
