<?php

namespace App\Console\Commands\CallManager;

use App\CucmRealTime;
use App\Cucmphoneconfigs;
use Illuminate\Console\Command;

class CucmPhoneIPAddresses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'callmanager:phone_ip_address_and_status_scan';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get Phone IPs and Registration status';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        // Construct new cucm object
        $this->CucmRealTime = new CucmRealTime();
		
        parent::__construct();
    }
	
	public $count; 

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

	
		Cucmphoneconfigs::chunk(1000, function ($phones) {
			
			$query = [];
			
			foreach($phones as $phone){
				$query[] = $phone->name;
			}
			
			if(count($query)){
				
			}
			$risdb_phones = $this->get_phone_ip($query);

            foreach ($phones as $phone) {
				
				$this->count++;
				
				//print_r($phone);
				$risdb_registration_status = '';
				$risdb_ipv4address = '';
                
				print "{$this->count}: Looking for Phone: {$phone->name}".PHP_EOL;
                //if (array_key_exists($phone->name, $this->egw_endpoints)) {
					
				if (array_key_exists($phone->name, $risdb_phones)) {
                    print "Found {$phone->name} in Risdb Phones...".PHP_EOL;
                    //print_r($this->egw_endpoints[$phone->name]);
                    $risdb_registration_status = $risdb_phones[$phone->name]['status'];
                    $risdb_ipv4address = $risdb_phones[$phone->name]['ipAddress'];
                    print $risdb_registration_status.PHP_EOL;
                    print $risdb_ipv4address.PHP_EOL;

                    if ($risdb_registration_status) {
                        $phone->fill(['risdb_registration_status' => $risdb_registration_status]);
                    }
                    if ($risdb_ipv4address) {
                        $phone->fill(['risdb_ipv4address' => $risdb_ipv4address]);
                    }

                    $phone->save();
                } else {
                    $phone->fill(['risdb_registration_status' => null]);
                    $phone->fill(['risdb_ipv4address' => null]);
                    $phone->save();
                }
				
            }
			print "Sleeping for 15 seconds...".PHP_EOL;
			sleep(15); 
            //die();
        });
    }

    public function get_phone_ip($phones)
    {
		$MAX_PHONE_QUERY = 1000;
		
		//$phones = ['tlriesenberg', 'SEP00DA5527F681', 'SEP0C6803C0AFC1', 'SEP000000000000'];
		
        $count = 0;

		$result = [];
		foreach($phones as $phone){
			$searchCriteria["SelectItem[$count]"]['Item'] = $phone;
			$count++;
			
			if ($count >= $MAX_PHONE_QUERY || $count >= count($phones)){
				$query_result = $this->CucmRealTime->getIPAddresses($searchCriteria);
				$result = array_merge($result, $query_result);
				$count = 0;
				//sleep(15); 
			}
		}

        // $result = $this->CucmRealTime->getIPAddresses($searchCriteria);

		print_r($result);
		print count($result).PHP_EOL;
		
		return $result;
    }
}
