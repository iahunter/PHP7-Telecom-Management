<?php

namespace App\Console\Commands\CallManager;

use Illuminate\Console\Command;
use App\Cucmphoneconfigs;
use Carbon\Carbon;

class CucmPhoneandNumberCleanup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'callmanager:available-phone-line-cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan CUCM and delete phones and lines with available in description';

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
		
		// Change this to true to run the cleanup after the report is generated. 
		$rundelete = true;
		
        try {
            $phones = $this->cucm->phone_search('description', '%available%');

            if (! count($phones)) {
                throw new \Exception('Indexed results from call mangler is empty');
            }
        } catch (\Exception $e) {
            echo 'Callmanager blew up: '.$e->getMessage().PHP_EOL;
            //dd($e->getTrace());
        }
		
		//print_r($phones);
		
		$phones_to_delete = [];
		$lines_to_delete = [];
		$count = 0;
		foreach($phones as $phone){
			print "##############################".PHP_EOL;
			$count++;
			print "{$count} of ". count($phones). " ". $phone['name'].PHP_EOL;
			
			$phone_config = \App\Cucmphoneconfigs::where('name', $phone['name'])->first();
			$phone_reg_status = $phone_config->risdb_registration_status;
			
			//print_r($phone_reg_status);
			if($phone_reg_status != null){
				print "Skipping {$phone['name']} because it's status is: {$phone_reg_status}".PHP_EOL;
				continue;
			}

			try {
				$phone = $this->cucm->get_phone_by_name($phone['name']);
				$phones_to_delete[] = $phone;
				
			} catch (\Exception $e) {
				echo 'Callmanager blew up: '.$e->getMessage().PHP_EOL;
				//dd($e->getTrace());
			}

			try {
				$line_details = $this->cucm->get_lines_details_by_phone_name($phone['name']);
				//print_r($line_details);
			} catch (\Exception $e) {
				echo 'Callmanager blew up: '.$e->getMessage().PHP_EOL;
				//dd($e->getTrace());
			}
			
			//print_r($line_details);
			
			foreach($line_details as $line){
				$number = $line['pattern'];
				$line_description = $line['description']; 
				$devices = $line['associatedDevices'];
				
				print $number.": ". $line_description.PHP_EOL;
				
				if(isset($devices['device'])){
					if(is_array($devices['device'])){
						print "Skipping {$number} as it has multiple devices assigned to it...".PHP_EOL;
						print_r($devices);
					}else{
						print_r($devices);
						$regex = '/available/';
						if(preg_match($regex, strtolower($line_description))){
							print "Match Found in {$line_description}".PHP_EOL;
							$lines_to_delete[] = $line;
						}
					}
				}
			}
			
		}
		$line_count = 0;
		$phone_count = 0;
		print "#############################################################".PHP_EOL;
		foreach($lines_to_delete as $line){
			$line_count++;
			print "Line: {$line_count} of ". count($lines_to_delete). ": {$line['pattern']} | {$line['description']}".PHP_EOL;
		}
		print "#############################################################".PHP_EOL;
		foreach($phones_to_delete as $phone){
			$phone_count++;
			print "Phone: {$phone_count} of ". count($phones_to_delete). ": {$phone['name']} | {$phone['description']}".PHP_EOL;
		}
		print "#############################################################".PHP_EOL;
		
		
		
		// If we set the variable to true, delete the objects it found. 
		if($rundelete == true){
			
			$line_count = 0;
			$phone_count = 0;
			
			$results = [];
			$results['lines'] = [];
			$results['phones'] = [];
			
			print "#############################################################".PHP_EOL;
			foreach($lines_to_delete as $line){
				$line_count++;
				print "Line: {$line_count} of ". count($lines_to_delete). ": {$line['pattern']} | {$line['description']}".PHP_EOL;
				
				// Delete the Line
				try {
					$result = $this->cucm->delete_object_type_by_uuid($line['uuid'], "Line");
					$results['lines'][$line['pattern']] = $line;
					
				} catch (\Exception $e) {
					echo 'Callmanager blew up: '.$e->getMessage().PHP_EOL;
					//dd($e->getTrace());
				}
			}
			
			print "#############################################################".PHP_EOL;
			foreach($phones_to_delete as $phone){
				$phone_count++;
				print "Phone: {$phone_count} of ". count($phones_to_delete). ": {$phone['name']} | {$phone['description']}".PHP_EOL;
				
				// Delete the Phone
				try {
					$result = $this->cucm->delete_object_type_by_uuid($phone['uuid'], "Phone");
					$results['phones'][$phone['name']] = $Phone;
					
				} catch (\Exception $e) {
					echo 'Callmanager blew up: '.$e->getMessage().PHP_EOL;
					//dd($e->getTrace());
				}
			}
			print "#############################################################".PHP_EOL;
			
			// Save Site Work as JSON
			
			print $start.PHP_EOL;
			$time = Carbon::now();
			print $time.PHP_EOL;
			
			$time = explode(" ", $time);
			
			$svn_save = json_encode($results, JSON_PRETTY_PRINT);
			echo 'Saving output json to file...'.PHP_EOL;
			
			file_put_contents(storage_path("cucm/linecleanup/deleted_available_phonesandlines_{$time[0]}_{$time[1]}.json"), $svn_save);
			
			echo "Complete".PHP_EOL;
		}
		
    }
	
}
