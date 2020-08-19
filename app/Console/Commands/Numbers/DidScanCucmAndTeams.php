<?php

namespace App\Console\Commands\Numbers;

use App\Did;
use App\Didblock;
use DB;
use Carbon\Carbon;
use App\Gizmo\RestApiClient as Gizmo;
use Illuminate\Console\Command;

class DidScanCucmAndTeams extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'numbers:didscan-cucm-teams';

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
		$starttime = Carbon::now();
        echo 'Starting - '.$starttime.PHP_EOL;

        // Get our list of NPA/NXX's
        $prefixes = $this->getDidNPANXXList();
		
		//$prefixes = ["1" => ["1001234"]];
		
		$prefix_count = 0; 
		$total_prefix_count = count($prefixes);
		

        $possible_deletes = [];
        // Loop through our NPA/NXX's and get their devices out of call wrangler
		foreach ($prefixes as $country_code => $npanxxs) {
			$prefix_count++;
			
			
			
			try {
				// Get the devices for this npa/nxx out of cucm
				$teamsdidinfo = $this->getTeamsEnterpriseVoiceUsers($country_code);
			} catch (\Exception $e) {
				echo 'Teams blew uP: '.$e->getMessage().PHP_EOL;
			}
			
			//print_r($teamsdidinfo); 
			
			$npanxx_count = 0;
			$total_npanxx_count = count($npanxxs);
			
			foreach ($npanxxs as $npanxx) {
				$npanxx_count++; 
				
				echo 'Start Time: '.$starttime.PHP_EOL;
				print 'Time: '. Carbon::now().PHP_EOL; 
				
				print "COUNTRY {$prefix_count} of {$total_prefix_count}".PHP_EOL;
				print "NPANXX {$npanxx_count} of {$total_npanxx_count}".PHP_EOL;
				
				$cucmdidinfo = [];
				
				try {
					// Get the devices for this npa/nxx out of cucm
					$cucmdidinfo = $this->getCucmDidsByNPANXX($country_code, $npanxx);
				} catch (\Exception $e) {
					echo 'Callmanager blew uP: '.$e->getMessage().PHP_EOL;
					dd($e->getTrace());
					// Stop if can't connect
					return;
				}
				
				// Only do this per NPANXX if we don't get all users above from Teams.
				if(!$teamsdidinfo){
					$teamsdidinfo = [];
					try {
						// Get the devices for this npa/nxx out of cucm
						$teamsdidinfo = $this->getTeamsDidsByNPANXX($country_code, $npanxx);
					} catch (\Exception $e) {
						echo 'Teams blew uP: '.$e->getMessage().PHP_EOL;
						dd($e->getTrace());
						// Stop if can't connect
						return;
					}
				}
				
				
				
				// Update all our DID information for this NPANXX based on those device records.
				
				//print_r($cucmdidinfo);
				//print_r($teamsdidinfo);
				
				$delete_numbers = $this->updateDidInfo($country_code, $npanxx, $cucmdidinfo, $teamsdidinfo);
				
				//print_r($delete_numbers);
				
				$possible_deletes[$country_code][$npanxx] = $delete_numbers; 
			}
		}

        // This will remove lines that are now available from the Line Cleanup Report.
        echo 'Starting Cleanup Quick Scan'.PHP_EOL;
        $this->updateNumberCleanupReport();
        echo 'Completed Cleanup Quick Scan'.PHP_EOL;
		
		echo 'Start Time: '.$starttime.PHP_EOL;
        echo 'Stop Time: '.Carbon::now().PHP_EOL;
    }

    // This gets a SIMPLE array of NPA/NXX for our numbers in the database.
    protected function getDidNPANXXList()
    {
        //$prefixes = Didblock::select(DB::raw('substring(start,1,6) as npanxx'))->groupBy(DB::raw('npanxx'))->get();
		
		$countrycodes = Didblock::select('country_code')->distinct()->get();
		
		//print_r($countrycodes); 
		$prefixes = []; 
		
		foreach($countrycodes as $country){
			$npanxxs = Didblock::select(DB::raw('substring(start,1,6) as npanxx'))
												->where('country_code', $country->country_code)
												->distinct()
												->get();
												
			foreach($npanxxs as $npanxx){
				$prefixes[$country->country_code][] = $npanxx->npanxx; 
			}
		}
		
        //$results = Didblock::select(DB::raw('substring(start,1,6) as npanxx'))->distinct()->get();
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
    protected function getCucmDidsByNPANXX($country_code, $npanxx)
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
                echo 'CUCM didinfo is blank!'.PHP_EOL;

                return $results;
            }
            foreach ($didinfo as $info) {
                if (! isset($results[$info['dnOrPattern']])) {
                    $results[$info['dnOrPattern']] = [];
                }
                $results[$info['dnOrPattern']][] = $info;
            }
            if (! count($results)) {
                throw new \Exception('Indexed results from call mangler are empty!!');
            }

			ksort($results); 

			$count = count($results); 
			print "Found: {$count} numbers in use in CUCM. ".PHP_EOL; 

			return $results;
			
            return $results;
        } catch (\Exception $e) {
            echo 'Callmanager blew uP: '.$e->getMessage().PHP_EOL;
            dd($e->getTrace());
        }
    }
	
	// Get the DID information for a single NPA/NXX and return a USEFUL array? key=>value by DID?
    protected function getTeamsDidsByNPANXX($country_code, $npanxx)
    {
        echo 'Getting NAPNXX: '.$npanxx.' numbers from Teams...'.PHP_EOL;
        try {
            $gizmo = new Gizmo(env('MICROSOFT_TENANT'), env('GIZMO_URL'), env('GIZMO_CLIENT_ID'), env('GIZMO_CLIENT_SECRET'), env('GIZMO_SCOPE'));
		
			$gizmo->get_oauth2_token(); 
			
			
            $teamsinfo = $gizmo->get_teams_csonline_all_users_by_NPA_NXX($country_code.$npanxx);
            
            // Process the junk we got back from call mangler and turn it into something useful
            $results = [];
            if (! $teamsinfo) {
                // Return blank array if no results in $teamsinfo.
                echo 'Teamsinfo is blank!'.PHP_EOL;
                return $results;
            }
            
        } catch (\Exception $e) {
            echo "Teams could not get users in use with {$npanxx}... ".$e->getMessage().PHP_EOL;
            dd($e->getTrace());
        }
		
		foreach ($teamsinfo as $user) {
			$number = $user['onPremLineURI']; 
			if (isset($user['onPremLineURI']) && $user['onPremLineURI']) {
				$number = strtolower($user['onPremLineURI']); 
				//print "Working on number: ". $number.PHP_EOL; 
				$count = count($country_code); 
				
				if(preg_match("/tel:\+{$country_code}/", $number, $matches)){
					$count = $count + 5; 
					$number = substr($number,$count);
				}elseif(preg_match("/tel:\+/", $number, $matches)){
					$count = 5;
					$number = substr($number,$count);
				}else{
					$count = 4;
					$number = substr($number,$count);
				}
			}
			
			if(!isset($results[$number])){
				$results[$number] = []; 
			}
			$results[$number][] = $user;
		}
		if (! count($results)) {
			throw new \Exception('Indexed results from Teams is empty!!');
		}
		
		
		ksort($results); 

		$count = count($results); 
		print "Found: {$count} numbers in use in Microsoft Teams. ".PHP_EOL; 

		return $results;
    }
	
	
	// Get the DID information for a single NPA/NXX and return a USEFUL array? key=>value by DID?
    protected function getTeamsEnterpriseVoiceUsers($country_code)
    {
        echo 'Getting all enterprise voice enabled users from Teams...'.PHP_EOL;
        try {
            $gizmo = new Gizmo(env('MICROSOFT_TENANT'), env('GIZMO_URL'), env('GIZMO_CLIENT_ID'), env('GIZMO_CLIENT_SECRET'), env('GIZMO_SCOPE'));
		
			$gizmo->get_oauth2_token(); 
			
			
            $teamsinfo = $gizmo->get_teams_csonline_users_voice_enabled();
            
            // Process the junk we got back from call mangler and turn it into something useful
            $results = [];
            if (! $teamsinfo) {
                // Return blank array if no results in $teamsinfo.
                echo 'Teamsinfo is blank!'.PHP_EOL;
                return $results;
            }
            
        } catch (\Exception $e) {
            echo "Teams could not get users that are enterprise voice enabled... ".$e->getMessage().PHP_EOL;
            dd($e->getTrace());
        }
		
		foreach ($teamsinfo as $user) {
			$number = $user['onPremLineURI']; 
			if (isset($user['onPremLineURI']) && $user['onPremLineURI']) {
				$number = strtolower($user['onPremLineURI']); 
				//print "Working on number: ". $number.PHP_EOL; 
				$count = count($country_code); 
				
				if(preg_match("/tel:\+{$country_code}/", $number, $matches)){
					$count = $count + 5; 
					$number = substr($number,$count);
				}elseif(preg_match("/tel:\+/", $number, $matches)){
					$count = 5;
					$number = substr($number,$count);
				}else{
					$count = 4;
					$number = substr($number,$count);
				}
			}
			
			if(!isset($results[$number])){
				$results[$number] = []; 
			}
			$results[$number][] = $user;
		}
		if (! count($results)) {
			throw new \Exception('Indexed results from Teams is empty!!');
		}
		
		
		ksort($results); 

		$count = count($results); 
		print "Found: {$count} numbers in use in Microsoft Teams. ".PHP_EOL; 

		return $results;
    }

    // This updates DID records with new information AND clears out no longer used phone numbers / sets them to available
    //protected function updateDidInfo($npanxx, $didinfo)
	protected function updateDidInfo($country_code, $npanxx, $cucmdidinfo, $teamsdidinfo)
    {

        // Return array of the numbers that we need to look into cleaning up.
        $possible_deletes = [];

        // Get the DID records matching $npanxx.'%' - Only Valid for NANP Numbers
        if (\App\Did::where([['number', 'like', $npanxx.'%']])->count()) {
            //$dids = \App\Did::where([['country_code', '=', 1], ['number', 'like', $npanxx.'%']])->get();
            // Removed country code from the search to fix Mexico numbers - 010219 - TR
            $dids = \App\Did::where([['country_code', $country_code], ['number', 'like', $npanxx.'%']])
                    ->get();
        }

        // Go through all the mathcing DID's and update them, OR set them to available
        // maybe WRAP this in a try/catch block to handle individual number update failures!
		
        foreach ($dids as $did) {
			$system_array = [];
			$assignments_array = [];
			$teams_assignments_array = [];
            //try {
                // Skip over excluded numbers. These may not be part of our block.
                if ($did->status == 'exclude') {
                    continue;
                }
                // SKIP updating OR making available DID's that are RESERVED!
                if ($did->status == 'reserved') {

                    // If its now built in the system, mark it as inuse.
                    if (isset($cucmdidinfo[$did->number])) {
						
                        //$did->assignments = $cucmdidinfo[$did->number];

                        $did->status = 'inuse';
						$systemid = 'CucmNA';
						$assignments_array[$systemid] = $cucmdidinfo[$did->number];
						$system_array[] = $systemid; 
					}
                    // If its now built in the system, mark it as inuse.
                    if (isset($teamsdidinfo[$did->number])) {
                        //$did->assignments = $teamsdidinfo[$did->number];
                        $did->status = 'inuse';
                        $systemid = 'MicrosoftTeams';
						$system_array[] = $systemid; 
						$assignments_array[$systemid] = $cucmdidinfo[$did->number];
						
                    }
					else {
                        // If not skip it and leave it as reserved.
                        continue;
                    }
                }
				
				// CONTINUE HERE ########
                // IF this DID IS in the results from call wrangler, update it!
				if (isset($teamsdidinfo[$did->number]) || isset($cucmdidinfo[$did->number])){
					if (isset($cucmdidinfo[$did->number])) {

						// Check if this number has any assigned devices... Need to move this functionality to its own command and schedule.
						foreach ($cucmdidinfo[$did->number] as $entry) {
							if (isset($entry['routeDetail']) && ! $entry['routeDetail']) {
								//print "{$entry['dnOrPattern']} - This number needs looked at!!!".PHP_EOL;
								$possible_deletes[$entry['uuid']] = $entry['dnOrPattern'];
							}
						}

						$did->status = 'inuse';
						$systemid = 'CucmNA';
						$system_array[] = $systemid;
						$assignments_array[$systemid] = $cucmdidinfo[$did->number];
					}
					// If its now built in the system, mark it as inuse.
					if (isset($teamsdidinfo[$did->number])) {
						$did->status = 'inuse';
						$systemid = 'MicrosoftTeams';
						$system_array[] = $systemid; 
						$assignments_array[$systemid] = $teamsdidinfo[$did->number];
					}
					
					//print_r($assignments_array); 
					
					
					
					$did->system_id = $system_array; 
					$did->assignments = $assignments_array; 
					//$did->system_id = json_encode($system_array); 
					//$did->assignments = json_encode($assignments_array); 
				}
				
				// Else mark it available. 
				else {
                    $did->assignments = null;
                    $did->status = 'available';
                    $did->system_id = null;
                    $did->mailbox = null;
                }
                //dd($did);
                $did->save();
			/*
            } catch (\Exception $e) {
                echo 'Exception processing one DID '.$did->number.' '.$e->getMessage().PHP_EOL;
            }*/
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
