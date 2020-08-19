<?php

namespace App\Listeners;

use App\Cucmclass;
use App\Events\Create_Line_Event;
use App\Events\Create_Phone_Event;
use App\Events\Update_Cucm_CallForward_To_Teams_Event; 
use App\PhoneMACD;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;


class Create_Line_Listener implements ShouldQueue
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
     * @param  Create_Line_Event  $event
     * @return void
     */
    public function handle(Create_Line_Event $event)
    {
        // Create Log Entry
        \Log::info('createLineListener', ['data' => $event->phone]);

        // Get the Task ID
        $task = PhoneMACD::find($event->taskid);
		
		$macd = $task->parent;

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

        $CREATEDBY = $task->created_by;

        try {
            $LOG = Cucmclass::add_cucm_line(
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

            // Update task to completed.
            $task->fill(['updated_by' => 'Telecom Management Server', 'status' => 'complete', 'json' => $LOG]);
            $task->save();

            // Create Log Entry
            \Log::info('createLineListener', ['created_by' => $CREATEDBY, 'status' => 'complete', 'log' => $LOG]);
			
			
        } catch (\Exception $e) {
            // Update the status with exception info.
            $task->fill(['updated_by' => 'Telecom Management Server', 'status' => 'error', 'json' => $e->getMessage()]);
            $task->save();

            \Log::info('createLineListener', ['created_by' => $CREATEDBY, 'status' => 'error', 'log' => $e->getMessage()]);

            // Fail the Job
            throw new \Exception($e->getMessage());
        }
		
		// Forward all Calls to Teams
		if (isset($event->phone['callfwd2teams'])) {
			if ($event->phone['callfwd2teams']) {
				$task = PhoneMACD::create(['type' => 'Forward All Calls to Teams', 'parent' => $macd, 'status' => 'job received']);
				$tasks[] = $task;
				$data['taskid'] = $task->id;
				$data['phone'] = $event->phone;
				
				\Log::info('create_Update_Cucm_CallForward_To_Teams_Event', ['created_by' => $CREATEDBY, 'data' => $data]);

				// Forward Number to Teams
				event(new Update_Cucm_CallForward_To_Teams_Event($data));
				
			}
		}

        // After line is built. Go ahead and trigger a build phone event.

        // Build Phone

        $task = PhoneMACD::find($event->taskid);

        $task = PhoneMACD::create(['type' => 'Add Phone', 'parent' => $task->parent, 'status' => 'job recieved', 'created_by' => $task->created_by]);
        $tasks[] = $task;

        $data['taskid'] = $task->id;
        $data['phone'] = $event->phone;

        // Testing of Events Controller
        event(new Create_Phone_Event($data));
    }
}
