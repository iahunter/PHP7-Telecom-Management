<?php

namespace App\Console\Commands\Numbers;

use Illuminate\Console\Command;
use App\Did;

class NumberSearch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'numbers:scan-numbers-usage';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan Number Database for Numbers';

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
		// I saved a file with a number on each line as my numberscan file. This will scan each to make sure they exist in the Number DB. If not they scan CUCM to see if they are in use and tells you. 
	
        if (! file_exists(storage_path('numbers/numberscan')) || ! is_readable(storage_path('numbers/numberscan'))) {
            return 'FILE IS NOT BEING LOADED FROM: '.$location;
        }

        $data = file_get_contents(storage_path('numbers/numberscan'));

        $numbers = explode(PHP_EOL, $data);

        print_r($numbers);
        //die();

        $dids = [];
		$dids['found'] = [];
		$dids['notfound'] = [];
		$count = 0;
		$found = 0;
		$notfound = 0;
		
        foreach ($numbers as $number_search) {
			$count++;
			$number_search = trim($number_search);
			
			//print $number_search.PHP_EOL; 

			
            // Search for DID by numberCheck if there are any matches.
            if (! Did::where('number', '=', $number_search)->count()) {
				$notfound++;
				print "{$count}: {$number_search} | Not Found".PHP_EOL;
				$dids['notfound'][$number_search] = false;
            } else {
                $did = Did::where('number', '=', $number_search)->get();
				$did = json_decode(json_encode($did), true);
                if ($did != '') {
					$found++;
					print "{$count}: {$number_search} | Found".PHP_EOL;
					$dids['found'][$number_search] = $did;
                }
            }
        }
		
		print_r($dids['notfound']);
		print "Did not find {$notfound} Numbers".PHP_EOL;
		
		$numbers = [];
		foreach($dids['notfound'] as $did => $value){
			$cucmnumber = $this->getnumber($did);
			$numbers[$did] = $cucmnumber;
		}
		
		print_r($numbers);
    }
	
	protected function getnumber($number)
    {
        //echo 'Getting Number: '.$number.' from CUCM...'.PHP_EOL;
        try {
            $cucm = new \CallmanagerAXL\Callmanager(env('CALLMANAGER_URL'),
                                                    storage_path(env('CALLMANAGER_WSDL')),
                                                    env('CALLMANAGER_USER'),
                                                    env('CALLMANAGER_PASS')
                                                    );
            $didinfo = $cucm->get_route_plan_by_name($number);
            unset($cucm);
            
			if($didinfo){
				print "{$number} Found in CUCM!!! Please add to the Number Database!!!".PHP_EOL; 
				return $didinfo;
			}else{
				print "{$number} Not Found in CUCM!!! Please add to the Number Database or Disconnect from Provider...".PHP_EOL; 
				return false;
			}

        } catch (\Exception $e) {
            echo 'Callmanager blew uP: '.$e->getMessage().PHP_EOL;
            dd($e->getTrace());
        }
    }
}
