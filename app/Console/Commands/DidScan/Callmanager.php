<?php

namespace App\Console\Commands\DidScan;

use Illuminate\Console\Command;
use App\Did;
use App\Didblock;
use DB;

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
     */
    public function handle()
    {
        // Get our list of NPA/NXX's
        $prefixes = $this->getDidNPANXXList();

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
            $this->updateDidInfo($npanxx, $didinfo);
        }
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
                throw new \Exception('Indexed results from call mangler are emptys!!111one');
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
		//print_r($didinfo);
		//die();
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
                    continue;
                }
                // IF this DID IS in the results from call wrangler, update it!
                if (isset($didinfo[$did->number])) {
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
    }
}
