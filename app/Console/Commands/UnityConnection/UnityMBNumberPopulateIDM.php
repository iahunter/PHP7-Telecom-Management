<?php

namespace App\Console\Commands\UnityConnection;

use App\Didblock;
use App\Did;
use App\SAP\IDM\RestApiClient;
use Illuminate\Console\Command;

class UnityMBNumberPopulateIDM extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cisco_unity_connection:populate_idm_with_mbox_number';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populate IDM users blank telephone number with with MBox Number from Unity';

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
		// Get users to update. 
		$users = $this->get_users_to_update(); 
		print_r($users); 
		$updates = []; 
		foreach($users as $user){
			print_r($user); 
			$username = $user['username']; 
			$number = $user['number']; 

			$updates[] = $this->update_idm_users($username, $number); 
		}
		
		print_r($updates); 
		
		foreach($updates as $update){
			print $update['username']. "Old Number: ". $update['current']. "New Number: " . $update['current'].PHP_EOL; 
		}
    }
	
	public function get_users_to_update()
	{
		$userstoupdate = []; 
        $blocks = Didblock::all();
		foreach($blocks as $block){
			//print $block['name'].PHP_EOL; 
			$dids = Did::where('parent', $block['id'])->get(); 
			foreach($dids as $did){
				//print_r($did['mailbox']); 
				$mailbox = $did['mailbox']; 
				
				if(isset($mailbox['User']) ){
					if($mailbox['User']['AD User']){
						print $mailbox['User']['Alias'].PHP_EOL; 
						
						if($mailbox['User']['Alias'])
						// Set the argument variables.
						$userid = $mailbox['User']['Alias'];
						$newdn = $mailbox['User']['DtmfAccessId'];

						$guid = env('IDM_GUID');
						
						// Create new API Client with Required arguments
						$client = new RestApiClient(env('IDM_URL'), env('IDM_USER'), env('IDM_PASS'));

						// Get User ID from username.
						$id = $client->getUserID($userid);
													
						if(!$id){
							continue; 
						}

						// Check what hte current phone number is set to.
						$number = $client->getUserPhone($id, $guid);
						print $number.PHP_EOL;

						$update = []; 
						if($number == null){
							print "**** Number is Null ****".PHP_EOL; 
							$update['username'] = $userid; 
							$update['number'] = $newdn; 
							$userstoupdate[] = $update; 
						}
					}
				}

				
			}
			
		}
		// Return Array of UserId and Number of users that phonenumber is null and needs updated. 
		return $userstoupdate; 
    }
	
	public function update_idm_users($user, $number){
		$userid = $user;
		$newdn = $number; 

		$guid = env('IDM_GUID');

		// Create new API Client with Required arguments
		$client = new RestApiClient(env('IDM_URL'), env('IDM_USER'), env('IDM_PASS'));

		// Get User ID from username.
		$id = $client->getUserID($userid);
									
		if(!$id){
			return []; 
		}

		// Check what hte current phone number is set to.
		$number = $client->getUserPhone($id, $guid);

		// Update the User Phone
		$number2 = $client->updateUserPhone($id, $guid, $newdn);

		// Check what hte current phone number is after the change
		$number3 = $client->getUserPhone($id, $guid);

		// Print out what the old number was and what it is now.
		echo 'OLD: '.$number.PHP_EOL;
		echo 'NEW: '.$number3.PHP_EOL;

		if ($number3 != $newdn) {
			throw new \Exception("The current number {$number3} after the change doesn't match the New Number: {$newdn}.");
		}
		
		$update = []; 
		$update['username'] = $user; 
		$update['new'] = $newdn; 
		$update['old'] = $number; 
		$update['current'] = $number3; 
		
		return $update; 
	}
}
