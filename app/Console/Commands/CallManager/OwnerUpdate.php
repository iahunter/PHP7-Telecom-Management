<?php

namespace App\Console\Commands\CallManager;

use Illuminate\Console\Command;

class OwnerUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'callmanager:owner-update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'DO NOT RUN!!! Custom Script - Updates OwnerID Field in CUCM';

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
	// Set this to true if you want to set unassigned devices to the "CallManager.Unassign" User if no match found. Set to False if you want it to be left Anonymous
    public $use_callmanager_unnassigned = true;
	
	// Set to true if you want to try to resolve CallManager.Unassign users by thier Phone Description
	public $resolve_unnassigned = true;
	
	/**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
		print "Starting callmanager:owner-update... ".PHP_EOL;
		
		$users = $this->cucm->get_all_users();
		//print_r($users);
		print "Found ".count($users)." Users in CUCM".PHP_EOL;
		
		// Do Stuff Here. 
		print "Getting Sites... ".PHP_EOL;
		$SITES = $this->cucm->get_site_names();
		$LEN = count($SITES);
		print "Found {$LEN} Sites in CUCM...".PHP_EOL;
		
		
		//$SITES = ["CENNEOMA"];
		$SITE_COUNT = 0;
		foreach ($SITES as $SITE){
			print "##########################################################################".PHP_EOL;
			$SITE_COUNT++;
			print "Working On Site: ". $SITE_COUNT .". ".$SITE.PHP_EOL;
			$phones = $this->cucm->list_all_phones_summary_by_site($SITE);
			//print_r($phones);
			print "Phone Count: ".count($phones) .PHP_EOL;
			//die();
			if(count($phones) > 1){
				$count = 0;
				$skipped = 0;
				$found = 0;
				$unassigned = 0;
				$skipped_unassigned = 0;
				$removed_unassigned = 0;
				foreach($phones as $phone){
					$new_owner = "";
					$count++;
					
					// Future if User is = to CallManager.Unassign then do something. 
					if($phone["ownerUserName"]["_"] == "CallManager.Unassign"){
						
						if ($this->resolve_unnassigned == true){
							// Try to find the user first by looking at the description. If found then set it. 
							$description  = explode(" ", $phone["description"]);
							if(count($description) > 1){
								$first_name = $description[0];
								$last_name = $description[1];
								foreach($users as $user){
									if ((strtolower($first_name) == strtolower($user["firstName"])) &&  (strtolower($last_name) == strtolower($user["lastName"]))){
										print "      We have a match!!! ". $phone["name"] . " | " .$phone["description"] . " | " .$first_name. " ". $last_name.PHP_EOL;
										$new_owner = $user["userid"];
										$found++;
									}
								}
							}
						}
						
						
						// If we have the global varible set to true then skip else set it to blank. 
						
						if ($new_owner == ""){
							if($this->use_callmanager_unnassigned == true){
							$skipped_unassigned++;
							continue;
							}
							
							if($this->use_callmanager_unnassigned != true){
								$removed_unassigned++;
							}
						}
						

						// Update Phone with new Owner ID. 
						
						$DATA = [	'name' => $phone["name"],
									'ownerUserName' => $new_owner
								];
								
						print $count.". {$phone["name"]} New Owner ID: ".$new_owner.PHP_EOL;
						print "Updating Phone...".PHP_EOL;
						$this->cucm->update_object_type_by_assoc($DATA, "Phone");

					}
					
					// If Owner is not blank then continue. 
					elseif($phone["ownerUserName"]["_"] != ""){
						$skipped++;
						//print $count.". Skipping ".$phone["name"].": ".$phone["ownerUserName"]["_"].PHP_EOL;
						continue;
					}
					
					// If Owner does not have a value then try to find the owner ID by parsing the description. 
					elseif ($phone["ownerUserName"]["_"] == ""){
						//print $phone["description"].PHP_EOL;
						$description  = explode(" ", $phone["description"]);
						if(count($description) > 1){
							$first_name = $description[0];
							$last_name = $description[1];
							foreach($users as $user){
								if ((strtolower($first_name) == strtolower($user["firstName"])) &&  (strtolower($last_name) == strtolower($user["lastName"]))){
									//print "      We have a match!!! ". $phone["name"] . " | " .$phone["description"] . " | " .$first_name. " ". $last_name.PHP_EOL;
									$new_owner = $user["userid"];
									$found++;
								}
							}
						}
						
						if(($new_owner == "") && ($this->use_callmanager_unnassigned == true)){
							$new_owner = "CallManager.Unassign";
							$unassigned++;
						}

						// Update Phone with new Owner ID. 
						
						$DATA = [	'name' => $phone["name"],
									'ownerUserName' => $new_owner
								];
								
						print $count.". {$phone["name"]} New Owner ID: ".$new_owner.PHP_EOL;
						print "Updating Phone...".PHP_EOL;
						$this->cucm->update_object_type_by_assoc($DATA, "Phone");

					}
				}
				
				print "{$SITE_COUNT}. {$SITE} Results: ". PHP_EOL .  "Skipped: ".$skipped.PHP_EOL . "Found: ".$found.PHP_EOL . "Unassigned: " . $unassigned. PHP_EOL . "Skipped Unassigned: " .$skipped_unassigned. PHP_EOL . "Removed Unassigned: " .$removed_unassigned. PHP_EOL ."Total: ".$count.PHP_EOL;
				
			}
		}
	}
}
