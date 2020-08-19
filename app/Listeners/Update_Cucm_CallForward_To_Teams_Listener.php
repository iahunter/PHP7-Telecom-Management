<?php

namespace App\Listeners;

use App\Events\Update_Cucm_CallForward_To_Teams_Event;
use App\PhoneMACD; 
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class Update_Cucm_CallForward_To_Teams_Listener implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
	private $cucm; 
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  Update_Cucm_CallForward_To_Teams_Event  $event
     * @return void
     */
    public function handle(Update_Cucm_CallForward_To_Teams_Event $event)
    {
		// Construct new cucm object
        $this->cucm = new \Iahunter\CallmanagerAXL\Callmanager(env('CALLMANAGER_URL'),
                                                    storage_path(env('CALLMANAGER_WSDL')),
                                                    env('CALLMANAGER_USER'),
                                                    env('CALLMANAGER_PASS')
                                                    );
		
		
        // Create Log Entry
        \Log::info('Update_Cucm_CallForward_To_Teams_Event', ['data' => $event->phone]);

        // Get the Task ID
        $task = PhoneMACD::find($event->taskid);

        // Update the status in the MACD Table.
        $task->fill(['updated_by' => 'Telecom Management Server', 'status' => 'entered queue']);
        $task->save();
		
		

        $DN = $event->phone['dn'];
		$PARTITION = 'Global-All-Lines';

        $CREATEDBY = $task->created_by;

        try {
			
			// Get current line settings.
			$line = $this->cucm->get_object_type_by_pattern_and_partition($DN, $PARTITION, 'Line');
			
			\Log::info('Update_Cucm_CallForward_To_Teams_Event', ['data' => $line]);
			
			if (! $line) {
				$line = 'Not Found';
				abort(404, 'No Line Found');
			} else {
				$uuid = $line['uuid'];
				$callForwardAll = $line['callForwardAll'];
			}

			//$CFA_DESTINATION = env("TEAMS_STEERING_DIGITS") . $DN; 
			$callForwardAll['destination'] = env("TEAMS_STEERING_DIGITS") . $DN;

			$PHONELINE_UPDATE = [
				'pattern'                          => $DN,
				'routePartitionName'               => $PARTITION,
				'callForwardAll'				=> $callForwardAll,
				
			];
		
			\Log::info('Update_Cucm_CallForward_To_Teams_Event', ['data' => $PHONELINE_UPDATE]);


			$LOG['result'] = $this->cucm->update_object_type_by_pattern_and_partition($PHONELINE_UPDATE, 'Line');
			\Log::info('Update_Cucm_CallForward_To_Teams_Event', ['data' => $LOG['result']]);
			
			$LOG['new'] = $this->cucm->get_object_type_by_pattern_and_partition($DN, $PARTITION, 'Line');
			\Log::info('Update_Cucm_CallForward_To_Teams_Event', ['data' => $LOG['result']]);

            // Update task to completed.
            $task->fill(['updated_by' => 'Telecom Management Server', 'status' => 'complete', 'json' => $LOG]);
            $task->save();

            // Create Log Entry
            \Log::info('Update_Cucm_CallForward_To_Teams_Event', ['created_by' => $CREATEDBY, 'log' => $LOG]);
        } catch (\Exception $e) {
            // Update the status with exception info.
            $task->fill(['updated_by' => 'Telecom Management Server', 'status' => 'error', 'json' => $e->getMessage()]);
            $task->save();

            \Log::info('Update_Cucm_CallForward_To_Teams_Event', ['created_by' => $CREATEDBY, 'status' => 'error', 'log' => $e->getMessage()]);

            // Fail the Job
            throw new \Exception($e->getMessage());
        }
    }
}
