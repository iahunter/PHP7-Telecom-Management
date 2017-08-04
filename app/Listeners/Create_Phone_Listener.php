<?php

namespace App\Listeners;

use App\Cucmclass;
use App\PhoneMACD;
use App\Events\Create_Phone_Event;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class Create_Phone_Listener implements ShouldQueue
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
     * @param  Create_Phone_Event  $event
     * @return void
     */
    public function handle(Create_Phone_Event $event)
    {
        // Create Log Entry
        \Log::info('createPhoneListener', ['data' => $event->phone]);
		
		// Get the Task ID
		$task = PhoneMACD::find($event->taskid);
		
		// Update the status in the MACD Table.
		$task->fill(['updated_by' => 'Telecom Management Server', 'status' => 'entered queue']);
		$task->save();
		
		
        $SITE = $event->phone['sitecode'];
        $DEVICE = $event->phone['device'];
        $NAME = $event->phone['name'];
        $FIRSTNAME = $event->phone['firstname'];
        $LASTNAME = $event->phone['lastname'];
        $USERNAME = $event->phone['username'];
        $DN = $event->phone['dn'];
        $EXTENSIONLENGTH = $event->phone['extlength'];
        $LANGUAGE = $event->phone['language'];
        $VOICEMAIL = $event->phone['voicemail'];

		// Do Work. 
        $LOG = Cucmclass::provision_cucm_phone_axl(
                                                $SITE,
                                                $DEVICE,
                                                $NAME,
                                                $FIRSTNAME,
                                                $LASTNAME,
                                                $USERNAME,
                                                $DN,
                                                $EXTENSIONLENGTH,
                                                $LANGUAGE,
                                                $VOICEMAIL
                                            );

        // Find Task record by id
        
		$task = PhoneMACD::find($event->taskid);
        
		$CREATEDBY = $task->created_by;

		// Update task to completed. 
        $task->fill(['updated_by' => 'Telecom Management Server', 'status' => 'complete', 'json' => $LOG]);
        $task->save();

		// Create Log Entry
        \Log::info('createPhoneListener', ['created_by' => $CREATEDBY, 'log' => $LOG]);
    }
}
