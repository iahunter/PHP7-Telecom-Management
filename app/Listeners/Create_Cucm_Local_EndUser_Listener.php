<?php

namespace App\Listeners;

use App\Cucmclass;
use App\PhoneMACD;
use App\Events\Create_Cucm_Local_EndUser_Event;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class Create_Cucm_Local_EndUser_Listener implements ShouldQueue
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
     * @param  Create_Cucm_Local_EndUser_Event  $event
     * @return void
     */
    public function handle(Create_Cucm_Local_EndUser_Event $event)
    {
        // Create Log Entry
        \Log::info('createCucmEndUserListener', ['data' => $event->phone]);

        // Get the Task ID
        $task = PhoneMACD::find($event->taskid);

        // Update the status in the MACD Table.
        $task->fill(['updated_by' => 'Telecom Management Server', 'status' => 'entered queue']);
        $task->save();

		$data = []; 
        $data['firstname'] = $event->phone['firstname'];
        $data['lastname'] = $event->phone['lastname'];
        $data['username'] = $event->phone['localuser'];
        $data['dn'] = $event->phone['dn'];

        $CREATEDBY = $task->created_by;

        try {
            $LOG = Cucmclass::add_user($data);

            // Update task to completed.
            $task->fill(['updated_by' => 'Telecom Management Server', 'status' => 'complete', 'json' => $LOG]);
            $task->save();

            // Create Log Entry
            \Log::info('createCucmEndUserListener', ['created_by' => $CREATEDBY, 'log' => $LOG]);
        } catch (\Exception $e) {
            // Update the status with exception info.
            $task->fill(['updated_by' => 'Telecom Management Server', 'status' => 'error', 'json' => $e->getMessage()]);
            $task->save();

            \Log::info('createCucmEndUserListener', ['created_by' => $CREATEDBY, 'status' => 'error', 'log' => $e->getMessage()]);

            // Fail the Job
            throw new \Exception($e->getMessage());
        }
    }
}
