<?php

namespace App\Console\Commands\Monitoring;

use Illuminate\Console\Command;
use App\Sonus5kCDR;
use Carbon\Carbon;
use DB;
use Mail;

class CucmSonusLoopMitigator extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'monitoring:cucm_sonus_loop_mitigator';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitors Sonus CDR Attempt Records for Large Call Spikes to a Called Number, Checks if number is forwarded to itself, and removes forward if it is';

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
		$now = Carbon::now()->toDateTimeString();
		print $now." cucm_sonus_loop_mitigator: Starting...".PHP_EOL;
		
		// Number days to figure in average. 
		$days = 90;

        $now = Carbon::now()->setTimezone('UTC');
        $start = $now->subDays($days);
        $end = Carbon::now()->setTimezone('UTC');
		
		// Get the count of the total number of calls in specified time frame. 
        $count = \App\Sonus5kCDR::whereBetween('disconnect_time', [$start, $end])->count();
		
		$average = $count / 90 / 24; 		// Get average over that time frame per hour. 

        // Get Sonus SBC top 10 Attempt Counts by Called Number. 
		$cdrs = Sonus5kCDR::list_last_hour_top_attempt_counts_by_called_number_report(); 
		
		$cdrs_array = json_decode(json_encode($cdrs), true); 
		
		
		$threashold = $average * 3; // Normal call volume is only 8hr of the day so we multiply by 3 to get full average per hour. 
		
		$now = Carbon::now()->setTimezone('UTC');
		print $now. " Hourly Average Call Count: ". $average . PHP_EOL;

		$cdrs = []; 
		
		$loops = 0;
		
		foreach($cdrs_array as $cdr){
			if($cdr['total'] > $threashold){			// If the number of calls meets the threashold, then check cucm for call forwarding to itself. 
				
				try {
					$routeplan = $this->cucm->get_route_plan_by_name($cdr['called_number']); 
				} catch (\Exception $e) {
					print $e->getMessage().PHP_EOL;
				}
				
				if(!$routeplan){
					continue; 
				}
				if($routeplan){
					$remove_forwarding = [];
					$lines = []; 
					foreach($routeplan as $line){
						try {
							$line = $this->cucm->get_object_type_by_uuid($line['uuid'], "Line"); 
							$lines[$line['uuid']] = $line; 
						} catch (\Exception $e) {
							print $e->getMessage().PHP_EOL;
						}
					}
					
					foreach($lines as $line){
						
						$cucm_array[$line['uuid']] = []; // Create new Array with the UUID as key
						
						$cucm_array[$line['uuid']]['uuid'] = $line['uuid']; 

						$cfa = $line['callForwardAll']['destination']; 
						
						if($cfa){
							$cucm_array[$line['uuid']]['callForwardAll'] = $cfa; 
							print "Line {$line['pattern']} Forwarded To: {$cfa}" . PHP_EOL; 

							$number = $line['pattern']; 
							
							//Check if the line is forwarded to itself with a 9 for external dialing. 
							if(preg_match("/9{$number}/", $cfa) || preg_match("/91{$number}/", $cfa)){
								
								print "WARNING!!!! This line is forwarded to itself!!!!".PHP_EOL; 
								$loops++;
								print_r($line); 
								$new_cfa = [
											'pattern' => $line['pattern'],
											"routePartitionName" => $line["routePartitionName"]["_"],
											"callForwardAll" => 	
														[
															'destination' 						=> "",
															'callingSearchSpaceName' 			=> $line["callForwardAll"]["callingSearchSpaceName"]["_"],
															'secondaryCallingSearchSpaceName' 	=> $line["callForwardAll"]["secondaryCallingSearchSpaceName"]["_"],
														],
											"uuid" => $line['uuid'],
											]; 
								
								
								
								$cucm_array[$line['uuid']]['callForwardAll_Update'] =  $new_cfa; 
							}

						}
						
					}
					
					$cdr['cucm'] = $cucm_array;
				}
				

				$cdrs[$cdr['called_number']] = $cdr; // Append to array
				
			}
		}

		
		// Unforwarded numbers here. 
		
		//print_r($cdrs);

		$fixed_loops = 0; 
		$unfixed_loops = 0;
		$didwork = false; 
		foreach($cdrs as $number => $cdr){
			if(isset($cdr['cucm']) && count($cdr['cucm'])){
				foreach($cdr['cucm'] as $cucmupdate){
					//print_r($cucmupdate); 
					if(isset($cucmupdate['callForwardAll_Update'])){
						if($cucmupdate['callForwardAll_Update']){
							$update = $cucmupdate['callForwardAll_Update']; 

							try {
								print "Trying to remove Call Forwarding on Line: {$cdr['called_number']}....".PHP_EOL; 
								
								$REPLY = $this->cucm->update_object_type_by_pattern_and_partition($update, 'Line');
								if($REPLY){
									$fixed_loops++; 
									$now = Carbon::now()->toDateTimeString();

									$cdr['cucm'][$cucmupdate['uuid']]['update_time'] = $now;
									$cdr['cucm'][$cucmupdate['uuid']]['update_confirm'] = $REPLY;
									$didwork = true; 
								}else{
									$unfixed_loops++;
								}
							} catch (\Exception $e) {
								$unfixed_loops++;
								print $e->getMessage().PHP_EOL;
							}
						}
					}
				}
			}
			$cdrs[$number] = $cdr; 
		}

		//print_r($cdrs);
		if($didwork){
			$now = Carbon::now()->toDateTimeString();
			print $now."cucm_sonus_loop_mitigator: Did work".PHP_EOL;
			
			$cdrs_json = json_encode($cdrs); 

			$data = [
						'time'                         	=> $now,
						'loops'                 		=> $loops,
						'fixed_loops'                   => $fixed_loops,
						'unfixed_loops'        			=> $unfixed_loops,
						'cdrs'                       	=> $cdrs,
						'cdrs_json'						=> $cdrs_json,
						];
			
			print_r($data);
			
			
			$this->sendemail($data); 
		}
		
		$now = Carbon::now()->toDateTimeString();
		print $now." cucm_sonus_loop_mitigator: Complete.".PHP_EOL;
		
    }
	
	public function sendemail($data)
    {
        // Send email to the Oncall threshold met.

        // The HTML View is in resources/views/cucmloopalarm.blade.php
        Mail::send(['html'=>'cucmloopalarm'], $data, function ($message) {
            $message->subject('Telecom Management Alert - Detected Routing Loop!')
                        //->from([env('MAIL_FROM_ADDRESS')])
                        //->to([env('ONCALL_EMAIL_TO')])
                        ->bcc([env('BACKUP_EMAIL_TO')]);
        });

        //echo 'Email sent to '.env('ONCALL_EMAIL_TO').PHP_EOL;
        echo 'Email sent to '.env('BACKUP_EMAIL_TO').PHP_EOL;
    }
	
	
}
