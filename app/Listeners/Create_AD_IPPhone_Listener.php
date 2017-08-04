<?php

namespace App\Listeners;

use App\PhoneMACD;
use App\Events\Create_AD_IPPhone_Event;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Http\Controllers\Auth\AuthController;

class Create_AD_IPPhone_Listener
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

        // Do Work.
        $LOG = $this->Auth->changeLdapPhone($USERNAME, $DN);

        $CREATEDBY = $task->created_by;

        // Update task to completed.
        $task->fill(['updated_by' => 'Telecom Management Server', 'status' => 'complete', 'json' => $LOG]);
        $task->save();

        // Create Log Entry
        \Log::info('createAdPhoneListener', ['created_by' => $CREATEDBY, 'log' => $LOG]);
    }
}
