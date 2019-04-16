<?php

namespace App\Console\Commands\IDM;

use App\SAP\IDM\RestApiClient;
use Illuminate\Console\Command;

class IdmUpdateUserPhone extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'idm:update_user_phone {userid} {newdn}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update IDM Phone Number for User';

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
		// Set the argument variables. 
		$userid = $this->argument('userid');
		$newdn = $this->argument('newdn');
		
		$guid = env('IDM_GUID'); 
		
		// Create new API Client with Required arguments
		$client = new RestApiClient(env('IDM_URL'), env('IDM_USER'), env('IDM_PASS')); 
		
		// Get User ID from username. 
		$id = $client->getUserID($userid); 
		
		// Check what hte current phone number is set to. 
		$number = $client->getUserPhone($id, $guid); 
		
		// Update the User Phone
		$number2 = $client->updateUserPhone($id, $guid, $newdn); 
		
		// Check what hte current phone number is after the change
		$number3 = $client->getUserPhone($id, $guid); 
		
		// Print out what the old number was and what it is now. 
		print "OLD: ".$number.PHP_EOL; 
		print "NEW: ".$number3.PHP_EOL; 

		if($number3 != $newdn){
			throw new \Exception("The current number {$number3} after the change doesn't match the New Number: {$newdn}."); 
		}
    }
}
