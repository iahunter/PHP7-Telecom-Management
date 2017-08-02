<?php

namespace App\Console\Commands\DidScan;

use DB;
use App\Did;
use App\Didblock;
use Illuminate\Console\Command;

class Callmanager extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'didscan:callmanager';

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
        $prefixes = [913689];
        $prefixes = [307232];

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

        // Return Array with numbers that could not be deleted because CFWA is set.
        $numbers_with_no_device_and_cfa_set = [];

        foreach ($possible_deletes as $npanxx => $cleanuparray) {
            echo $npanxx.'| Count: '.count($cleanuparray).PHP_EOL;
            //print_r($cleanuparray);
            foreach ($cleanuparray as $uuid => $number) {
                $line = $this->cleanup_number($uuid, $number);
                if ($line) {
                    $numbers_with_no_device_and_cfa_set[] = $line;
                }
            }
        }

        //
        print_r($numbers_with_no_device_and_cfa_set);
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
            $cucm = new \CallmanagerAXL\Callmanager(env('CALLMANAGER_URL'),
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
            $cucm = new \CallmanagerAXL\Callmanager(env('CALLMANAGER_URL'),
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
            $dids = \App\Did::where([['country_code', '=', 1], ['number', 'like', $npanxx.'%']])->get();
        }

        // Go through all the mathcing DID's and update them, OR set them to available
            // maybe WRAP this in a try/catch block to handle individual number update failures!

        foreach ($dids as $did) {
            try {
                // SKIP updating OR making available DID's that are RESERVED!
                if ($did->status == 'reserved') {
                    //continue;
                    if (isset($didinfo[$did->number])) {
                        $did->assignments = $didinfo[$did->number];
                        $did->status = 'inuse';
                        $did->system_id = 'CUCM-Enterprise-Cluster';
                    } else {
                        continue;
                    }
                }

                // IF this DID IS in the results from call wrangler, update it!
                if (isset($didinfo[$did->number])) {
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
                    $did->system_id = '';
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
}
