<?php

namespace App\Listeners;

use App\PhoneMACD;
use App\SAP\IDM\RestApiClient;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Events\Update_IDM_PhoneNumber_Event;

class Update_IDM_PhoneNumber_Listener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        $this->guid = env('IDM_GUID');

        // Create new API Client with Required arguments
        $this->client = new RestApiClient(env('IDM_URL'), env('IDM_USER'), env('IDM_PASS'));
    }

    /**
     * Handle the event.
     *
     * @param  Update_IDM_PhoneNumber_Event  $event
     * @return void
     */
    public function handle(Update_IDM_PhoneNumber_Event $event)
    {

        // Create Log Entry
        \Log::info('updateIDMPhoneNumberEvent', ['data' => $event->phone]);

        // Get the Task ID
        $task = PhoneMACD::find($event->taskid);

        // Update the status in the MACD Table.
        $task->fill(['updated_by' => 'Telecom Management Server', 'status' => 'entered queue']);
        $task->save();

        $username = $event->phone['username'];
        $newdn = $event->phone['dn'];

        $createdby = $task->created_by;

        $guid = $this->guid;

        // Try to Do Work.
        try {
            // Get User ID from username.
            $id = $this->client->getUserID($username);

            // Check what hte current phone number is set to.
            $number = $this->client->getUserPhone($id, $guid);

            // Update the User Phone
            $number2 = $this->client->updateUserPhone($id, $guid, $newdn);

            // Check what hte current phone number is after the change
            $number3 = $this->client->getUserPhone($id, $guid);

            // Print out what the old number was and what it is now.
            $LOG['old'] = $number;
            $LOG['new'] = $number3;


            // Update task to completed.
            $task->fill(['updated_by' => 'Telecom Management Server', 'status' => 'complete', 'json' => $LOG]);
            $task->save();

            // Create Log Entry
            \Log::info('updateIDMPhoneNumberListener', ['created_by' => $createdby, 'log' => $LOG]);
        } catch (\Exception $e) {
            // Update the status with exception info.
            $task->fill(['updated_by' => 'Telecom Management Server', 'status' => 'error', 'json' => $e->getMessage()]);
            $task->save();
            \Log::info('updateIDMPhoneNumberListener', ['created_by' => $createdby, 'log' => $e->getMessage()]);

            // Fail the Job
            throw new \Exception($e->getMessage());
        }
    }
}
