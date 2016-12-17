<?php

namespace App\Console\Commands\UnityConnection;

use Illuminate\Console\Command;
use App\Http\Controllers\Cucmphone;
use App\Cupiuser;

class UnityConnMB extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'unityconnection:import-mailboxes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Unity Mailboxes from LDAP';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
		// Construct new cucmphone controller to call function. May move this to a model later. 
		$this->cucmphone = new Cucmphone;
			
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
	
	// Do you want to override the extension number of the mailbox if it is already set? If so change $override to true
	public $override = false;
	
	
	
	/*********************************************************
	* Set the User Template to be used for import. 
	*
	* Change this every time you import a different Site!!! 
	*********************************************************/
	public $user_template;
	 
    public function handle()
    {
		
		// Paste in your users into this file from excel to be parsed and imported. 
		require __DIR__.'/../CallManager/Imports/Phones.txt';
		
		$this->user_template = $user_template;
		$phones = $this->cucmphone->phones_string_to_array($phones);
		
		//print_r($phones);
		
		$usersarray = [];
		//$alias = "travis.riesenberg";
		foreach($phones as $phone){
			//print_r($phone);
			
			if(($phone['voicemail'] != 'Y') && ($phone['voicemail'] != 'Yes')){
				print "No Voicemail for {$phone['username']}...".PHP_EOL;
				continue;
			}
			
			$userarray = [];
			$userarray['username'] = $phone['username'];
			$userarray['new_dn'] = $phone['dn'];
			$mailbox = Cupiuser::finduserbyalias($phone['username']);
			if (isset($mailbox['User']['ObjectId'])){
				$userarray['ObjectId'] = $mailbox['User']['ObjectId'];
			
				//print_r($mailbox);
				if (isset($mailbox['User']['DtmfAccessId'])){
					$userarray['old_dn'] = $mailbox['User']['DtmfAccessId'];
				
					if(($this->override) && ($userarray['old_dn'] != $userarray['new_dn'])){
						print_r($mailbox);
						$ID = $userarray['ObjectId'];
						$UPDATE = ['DtmfAccessId' => $userarray['new_dn']];
						//$UPDATE = ['City' => $userarray['username']];
						print_r($UPDATE);
						$UPDATED = Cupiuser::updateUserbyobjectid($ID, $UPDATE);
						print_r($UPDATED);
						$userarray['updatemailbox'] = $UPDATED;
						
						print_r($userarray);
					}
				
				}
			}else{
				print "Finding User {$userarray['username']}...".PHP_EOL;
				$LDAPUSER = Cupiuser::getLDAPUserbyAlias($userarray['username']);
				print_r($LDAPUSER);
				if($LDAPUSER['@total'] >= 1){
					print "User Found: Importing User {$userarray['username']}...".PHP_EOL;
					if ($LDAPUSER['@total'] == 1){
						$userarray['ldap'] = $LDAPUSER['ImportUser'];
					}
					elseif ($LDAPUSER['@total'] > 1){
						foreach($LDAPUSER['ImportUser'] as $USER){
							print_r($USER);
							if ($USER['alias'] == $userarray['username']){
								$userarray['ldap'] = $USER;
							}
						}
						
					}

					// Import User with Site Template.
					$userarray['ldap']['dtmfAccessId'] = $userarray['new_dn'];
					//print_r($userarray);
					$IMPORT = Cupiuser::importLDAPUser($this->user_template, $userarray['ldap']);
					$userarray['user_imported'] = $IMPORT;
				}elseif($LDAPUSER['@total'] == 0){
					$userarray['error'] = 'No User Found';
				}
			}
			
			
			$usersarray[] = $userarray;

		}
		
		print_r($usersarray);
			
	}
	
	
}
