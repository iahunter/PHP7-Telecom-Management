<?php

namespace App\Listeners;

use App\Cupi;
use App\PhoneMACD;
use App\Events\Create_UnityConnection_LDAP_Import_Mailbox_Event;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class Create_UnityConnection_LDAP_Import_Mailbox_Listener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  Create_UnityConnection_LDAP_Import_Mailbox_Event  $event
     * @return void
     */
    public function handle(Create_UnityConnection_LDAP_Import_Mailbox_Event $event)
        {
		// Create Log Entry
        \Log::info('createUnityMailboxLDAPUserListener', ['data' => $event->phone]);
		
		// Get the Task ID
		$task = PhoneMACD::find($event->taskid);
		
		// Update the status in the MACD Table.
		$task->fill(['updated_by' => 'Telecom Management Server', 'status' => 'entered queue']);
		$task->save();
		
        $USERNAME = $event->phone['username'];
        $DN = $event->phone['dn'];
		$TEMPLATE = $event->phone['template'];

		// Do Work. 
        $LOG = Cupi::importLDAPUser($USERNAME, $DN, $TEMPLATE, $OVERRIDE = '');

        // Find Task record by id
        
		$task = PhoneMACD::find($event->taskid);
        
		$CREATEDBY = $task->created_by;

		// Update task to completed. 
        $task->fill(['updated_by' => 'Telecom Management Server', 'status' => 'complete', 'json' => $LOG]);
        $task->save();

		// Create Log Entry
        \Log::info('createUnityMailboxLDAPUserListener', ['created_by' => $CREATEDBY, 'log' => $LOG]);
    }
}
