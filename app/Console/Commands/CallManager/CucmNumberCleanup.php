<?php

namespace App\Console\Commands\CallManager;

use App\Did;
use App\Didblock;
use App\Cupi;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CucmNumberCleanup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'callmanager:cleanup_unused_numbers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleanup Numbers in Cucm that have no Devices assigned, No CallForwarding Acive, and No Mailbox in Cisco Unity';

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

        $this->svn = env('CUCM_SVN');

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
		
		print $start;
		
        $didblocks = \App\Didblock::where([['country_code', '=', 1]])->get();

		$count = 0; 
        $possible_deletes = [];
        foreach ($didblocks as $didblock) {

             // Get the DID records matching $npanxx.'%' - Only Valid for NANP Numbers
            if (\App\Did::where([['parent', '=', $didblock->id]])->count()) {
                $dids = \App\Did::where([['parent', '=', $didblock->id]])->get();

                $dids = json_decode(json_encode($dids, true));

                foreach ($dids as $did) {
                    if ($did->status == 'inuse') {
                        // Check if this number has any assigned devices... Need to move this functionality to its own command and schedule.
                        $did = (array) $did;
                        //print_r($did);
                        foreach ($did['assignments'] as $entry) {
                            $entry = (array) $entry;
                            if (isset($entry['routeDetail']) && ! $entry['routeDetail']) {
                                //print "{$entry['dnOrPattern']} - This number needs looked at!!!".PHP_EOL;
                                $possible_deletes[$didblock->id][$entry['uuid']] = $entry['dnOrPattern'];
								$count++;
                            }
                        }
                    }
                }
            }
        }

        print_r($possible_deletes);
		print "Found {$count} numbers".PHP_EOL; 
		
		$TYPE = "Line";
		$lines_to_delete = [];
		$lines_to_delete_count = 0;
		$lines_with_cfa_active = [];
		$lines_with_cfa_active_count = 0;
		$lines_with_mailbox_built = [];
		$lines_with_mailbox_built_count = 0;
		foreach($possible_deletes as $line){
			foreach($line as $uuid => $number){
				//$uuid = $uuid[0];
				try {
					$linedetails = $this->cucm->get_object_type_by_uuid($uuid, $TYPE);
				
					$mailbox = Cupi::findmailboxbyextension($linedetails['pattern']);
					
					if($mailbox['response']['@total'] > 0){
						$mailbox = true;
					}else{
						$mailbox = false;
					}
					
					// Check if CFA is set. 
					
					$line_summary = 	[
										'uuid' 				=> $linedetails['uuid'],
										'pattern' 			=> $linedetails['pattern'],
										'callForwardAll' 	=> $linedetails['callForwardAll']['destination'],
										'description'		=> $linedetails['description'],
										'associatedDevices'	=> $linedetails['associatedDevices'],
										'mailbox'			=> $mailbox,
										];
					
					if($linedetails['callForwardAll']['destination'] == ""){
						
						if(!$mailbox){
							$linesToDelete[$linedetails['uuid']] = $line_summary;
							
							print_r($linesToDelete[$linedetails['uuid']]);
							print "{$linedetails['pattern']} is Ready to Delete...".PHP_EOL;
							
							// Call Delete Function here... 
							
						}
						if($mailbox){
							$lines_with_mailbox_built[$linedetails['uuid']] = $line_summary;
							
							print_r($lines_with_mailbox_built[$linedetails['uuid']]);
							print "{$linedetails['pattern']} is has a mailbox built and cannot delete...".PHP_EOL;
						}
						
																
					}elseif($linedetails['callForwardAll']['destination'] != ""){
						
						$lines_with_cfa_active[$linedetails['uuid']] = $line_summary;
						
						print "{$linedetails['pattern']} is Forwarded to: {$linedetails['callForwardAll']['destination']}...".PHP_EOL;
						
					}else{
						print "Something jacked up... {$uuid} {$number}".PHP_EOL;
					}
				}
				catch (\Exception $e) {
					echo $e->getMessage();
					print "Call Mnaager blew up".PHP_EOL;
					continue;
				}
				
				
				
				
				
				
				
					
				//print_r($linedetails);
			}
		}
		
		$results = [];
		$results['lines_to_delete']  = $lines_to_delete;
		$results['lines_with_mailbox_built_count']  = $lines_with_mailbox_built;
		$results['lines_with_cfa_active']  = $lines_with_cfa_active;
		
		print "###########################################################################";
		
		print "lines_to_delete: {$lines_to_delete_count}".PHP_EOL;
		print "lines_with_mailbox_built_count: {$lines_with_mailbox_built_count}".PHP_EOL;
		print "lines_to_delete_count: {$lines_to_delete_count}".PHP_EOL;
		
		$end = Carbon::now();
        echo PHP_EOL;
        echo 'Start Time: '.$start.PHP_EOL;
        echo 'End Time: '.$end.PHP_EOL;
		
		
		print "###########################################################################";
		
		 // Save Site Config as JSON and upload to subversion for change tracking.
        $svn_save = json_encode($results, JSON_PRETTY_PRINT);
        
		echo "Saving output json to file...".PHP_EOL;
        
		file_put_contents("storage/cucm/linecleanup/report", $svn_save);
		
        echo "Saved to file...".PHP_EOL;

    }
}
