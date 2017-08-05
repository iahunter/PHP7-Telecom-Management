<?php

namespace App\Listeners;

use App\PhoneMACD;
use App\Events\Create_AD_IPPhone_Event;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Http\Controllers\Auth\AuthController;

class Create_AD_IPPhone_Listener implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        // Create new Auth Controller for LDAP functions.
        $this->Auth = new AuthController();
    }

    /**
     * Handle the event.
     *
     * @param  Create_AD_IPPhone_Event  $event
     * @return void
     */
    public function handle(Create_AD_IPPhone_Event $event)
    {
        // Create Log Entry
        \Log::info('createAdPhoneListener', ['data' => $event->phone]);

        // Get the Task ID
        $task = PhoneMACD::find($event->taskid);

        // Update the status in the MACD Table.
        $task->fill(['updated_by' => 'Telecom Management Server', 'status' => 'entered queue']);
        $task->save();

        $USERNAME = $event->phone['username'];
        $DN = $event->phone['dn'];

        $CREATEDBY = $task->created_by;

        // Try to Do Work.
        try {
            $LOG = $this->Auth->changeLdapPhone($USERNAME, $DN);

            // Update task to completed.
            $task->fill(['updated_by' => 'Telecom Management Server', 'status' => 'complete', 'json' => $LOG]);
            $task->save();

            // Create Log Entry
            \Log::info('createAdPhoneListener', ['created_by' => $CREATEDBY, 'log' => $LOG]);
        } catch (\Exception $e) {
            // Update the status with exception info.
            $task->fill(['updated_by' => 'Telecom Management Server', 'status' => 'error', 'json' => $e->getMessage()]);
            $task->save();
            \Log::info('createAdPhoneListener', ['created_by' => $CREATEDBY, 'log' => $e->getMessage()]);

            // Fail the Job
            throw new \Exception($e->getMessage());
        }
    }
}
