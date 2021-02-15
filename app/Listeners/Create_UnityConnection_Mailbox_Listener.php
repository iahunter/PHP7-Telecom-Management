<?php

namespace App\Listeners;

use App\Cupi;
use App\Events\Create_UnityConnection_Mailbox_Event;
use App\PhoneMACD;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class Create_UnityConnection_Mailbox_Listener implements ShouldQueue
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
     * @param  Create_UnityConnection_Mailbox_Event  $event
     * @return void
     */
    public function handle(Create_UnityConnection_Mailbox_Event $event)
    {
        // Create Log Entry
        \Log::info('createUnityMailboxListener', ['data' => $event->phone]);

        // Get the Task ID
        $task = PhoneMACD::find($event->taskid);

        // Update the status in the MACD Table.
        $task->fill(['updated_by' => 'Telecom Management Server', 'status' => 'entered queue']);
        $task->save();

        $USERNAME = $event->phone['username'];
        $DN = $event->phone['dn'];
        $TEMPLATE = $event->phone['template'];

        $CREATEDBY = $task->created_by;

        // Try to Do Work.
        try {
            $LOG = Cupi::createuser($USERNAME, $DN, $TEMPLATE);

            // Update task to completed.
            $task->fill(['updated_by' => 'Telecom Management Server', 'status' => 'complete', 'json' => $LOG]);
            $task->save();

            // Create Log Entry
            \Log::info('createUnityMailboxListener', ['created_by' => $CREATEDBY, 'log' => $LOG]);
        } catch (\Exception $e) {
            // Update the status with exception info.
            $task->fill(['updated_by' => 'Telecom Management Server', 'status' => 'error', 'json' => $e->getMessage()]);
            $task->save();

            \Log::info('createUnityMailboxListener', ['created_by' => $CREATEDBY, 'log' => $e->getMessage()]);

            // Fail the Job
            throw new \Exception($e->getMessage());
        }
    }
}
