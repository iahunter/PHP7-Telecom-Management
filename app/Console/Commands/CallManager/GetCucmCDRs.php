<?php

namespace App\Console\Commands\CallManager;

use Carbon\Carbon;
use phpseclib\Net\SFTP as Net_SFTP;

use Illuminate\Console\Command;
use App\CucmCDR;

class GetCucmCDRs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'callmanager:getcdrs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get CDR Records from CUCM and insert into the DB';

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
        //
		$this->get_cdrs_from_file();
    }
	
	
	
	public static function get_cdrs_from_file()
    {
		
		$sftp = new Net_SFTP(env('CUCMCDR_SERVER'));
        if (! $sftp->login(env('CUCMCDR_USER'), env('CUCMCDR_PASS'))) {
            exit('Login Failed');
        }

		$logs = CucmCDR::get_cdr_log_names();
		
		foreach($logs as $location){
			
			$currentfile = $sftp->get($location);

			$currentfile = explode(PHP_EOL, $currentfile);

			$headers = array_shift($currentfile);  // Trim off the Headers
			$headers = explode(',', $headers); // Convert to Array
			
			//print_r($headers);
			array_shift($currentfile); // Trim off the Types
			array_pop($currentfile); // Trim off the last line which is blank.
			
			$count = count($currentfile); 
			print "Found {$count} Records... ".PHP_EOL;
			print_r($currentfile); 

			foreach ($currentfile as $callrecord) {

				$raw = $callrecord; 
				
				// Parse record to the the record type.
				$callrecord = explode(',', $callrecord);
				
				//print_r($callrecord);
				
				if(count($callrecord) > 1){
					
					//try{
						
						$callrecord_array = [];
						$callrecord_array = CucmCDR::cdr_key_map_to_headers($headers, $callrecord); 
						
						$INSERT = [];
						
						$INSERT['globalCallID_callId'] = $callrecord_array["globalCallID_callId"];
						
						$INSERT['dateTimeConnect'] = $callrecord_array["dateTimeConnect"];
						$INSERT['dateTimeDisconnect'] = $callrecord_array["dateTimeDisconnect"];
						$INSERT['duration'] = $callrecord_array["duration"];
						$INSERT['callingPartyNumber'] = $callrecord_array["callingPartyNumber"];
						
						$INSERT['originalCalledPartyNumber'] = $callrecord_array["originalCalledPartyNumber"];
						$INSERT['finalCalledPartyNumber'] = $callrecord_array["finalCalledPartyNumber"];
						
						$INSERT['origDeviceName'] = $callrecord_array["origDeviceName"];
						$INSERT['destDeviceName'] = $callrecord_array["destDeviceName"];
						$INSERT['origIpv4v6Addr'] = $callrecord_array["origIpv4v6Addr"];
						$INSERT['destIpv4v6Addr'] = $callrecord_array["destIpv4v6Addr"];
						
						$INSERT['originalCalledPartyPattern'] = $callrecord_array["originalCalledPartyPattern"];
						$INSERT['finalCalledPartyPattern'] = $callrecord_array["finalCalledPartyPattern"];
						$INSERT['lastRedirectingPartyPattern'] = $callrecord_array["lastRedirectingPartyPattern"];
						
						$INSERT['raw'] = $raw;
						print_r($INSERT);
						//return $STATS;

						$result = CucmCDR::create($INSERT);
						
						print_r($callrecord_array);
					/*
					}catch (\Exception $e) {
						print "Error: ";
						print $e->getMessage(); 
					}
					*/
				
				
				}else{
					
					print "Count not greater than 1... Discarding record...".PHP_EOL;
					
				}
				

			}
			
			// Delete the file when we are done with it. 
			print "Attempting to Delete: ".$location.PHP_EOL;
			$deleted = $sftp->delete($location, false);
			if($deleted){
				print "Deleted: ".$location.PHP_EOL;
			}else{
				print "Failed to Delete: ".$location.PHP_EOL;
			}
			
			//die();
		}
        

        // Return the raw comma seperated Log entries not an array for the last 2 days.
        //return implode(PHP_EOL, $lasttwodays_calls);
    }
}
