<?php

namespace App\Listeners;

use App\Events\Update_Teams_User_For_Voice_Event;
use App\Gizmo\RestApiClient as Gizmo;
use App\PhoneMACD;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class Update_Teams_User_For_Voice_Listener implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     *
     * @param  Update_Teams_User_For_Voice_Event  $event
     * @return void
     */
    public function handle(Update_Teams_User_For_Voice_Event $event)
    {
        //Create Gizmo API Client to update Teams
        $this->client = new Gizmo(env('MICROSOFT_TENANT'), env('GIZMO_URL'), env('GIZMO_CLIENT_ID'), env('GIZMO_CLIENT_SECRET'), env('GIZMO_SCOPE'));

        // Create Log Entry
        \Log::info('updateTeamsUserForVoiceEvent', ['data' => $event->phone]);

        \Log::info('updateTeamsUserForVoiceListener', ['data' => $event->phone]);

        // Get the Task ID
        $task = PhoneMACD::find($event->taskid);

        \Log::info('updateTeamsUserForVoiceListenerTask', ['data' => $task]);

        // Update the status in the MACD Table.
        $task->fill(['updated_by' => 'Telecom Management Server', 'status' => 'entered queue']);
        $task->save();

        $userid = $event->phone['username'];
        $newdn = $event->phone['dn'];
		
		if(isset($event->phone['userprincipalname']) && $event->phone['userprincipalname']){
			$userprincipalname = $event->phone['userprincipalname']; 
		}else{
			$userprincipalname = null; 
		}

        $createdby = $task->created_by;

        // Try to Do Work.
        try {
            // Get User ID from username.
            \Log::info('updateTeamsUserForVoiceEvent', ['log' => 'Entered Try']);
			
			// Check if userprincipalname is passed in and is set. 
			if($userprincipalname){
				$user = $this->client->get_teams_csonline_user_by_userid($userprincipalname);
				\Log::info('updateTeamsUserForVoiceEvent', ['getuser' => $userprincipalname]);
			}
			else{
				$user = $this->client->get_teams_csonline_user_by_userid($userid);
				\Log::info('updateTeamsUserForVoiceEvent', ['getuser' => $user]);
			}
            
			\Log::info('updateTeamsUserForVoiceEvent', ['gotuser' => $user]);
			
            foreach ($user as $u) {
				if (isset($u['userPrincipalName']) && $u['userPrincipalName']) {
					
					$upn = $u['userPrincipalName']; 
					\Log::info('updateTeamsUserForVoiceEvent', ['userprincipalname' => $upn]);
                }
				
				if (isset($u['sipAddress']) && $u['sipAddress']) {
					$sipaddress = $u['sipAddress'];
					break; 
				} else {
					$domain = env('DOMAIN');
					$sipaddress = "sip:{$userid}@{$domain}";
					break; 
				}
            }
            // Check what hte current phone number is set to.

            $teams = [
                'Alias'                  => "{$userid}",
				//'UserPrincipalName'		 => "{$upn}",
                'SipAddress'             => $sipaddress,
                'OnPremLineURI'          => "tel:+1{$newdn}",
                'EnterpriseVoiceEnabled' => 'True',
                'HostedVoiceMail'        => 'True',
            ];

            $body = json_encode($teams);

            \Log::info('updateTeamsUserForVoiceEvent', ['setuser' => $body]);
            $teamsuser = $this->client->set_teams_user($body);
            \Log::info('updateTeamsUserForVoiceEvent', ['setuser' => $teamsuser]);
            // Check user after the set.
			
			// Check if userprincipalname is passed in and is set. 
			if($userprincipalname){
				$user2 = $this->client->get_teams_csonline_user_by_userid($userprincipalname);
				\Log::info('updateTeamsUserForVoiceEvent', ['getuser' => $userprincipalname]);
			}
			else{
				$user2 = $this->client->get_teams_csonline_user_by_userid($userid);
				\Log::info('updateTeamsUserForVoiceEvent', ['getuser' => $user2]);
			}

            // Print out what the old number was and what it is now.
            $LOG['old'] = $user;
            $LOG['new'] = $user2;

            // Update task to completed.
            $task->fill(['updated_by' => 'Telecom Management Server', 'status' => 'complete', 'json' => $LOG]);
            $task->save();

            // Create Log Entry
            \Log::info('updateTeamsUserForVoiceListener', ['created_by' => $createdby, 'log' => $LOG]);
        } catch (\Exception $e) {
            // Update the status with exception info.
            $task->fill(['updated_by' => 'Telecom Management Server', 'status' => 'error', 'json' => $e->getMessage()]);
            $task->save();
            \Log::info('updateTeamsUserForVoiceListener', ['created_by' => $createdby, 'log' => $e->getMessage()]);

            // Fail the Job
            throw new \Exception($e->getMessage());
        }
    }
}
