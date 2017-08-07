<?php

namespace App\Providers;

// Include MACD App for failed job updates.
use App\PhoneMACD;
use Illuminate\Support\Facades\Queue;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Queue::failing(function (JobFailed $event) {
            // $event->connectionName
            // $event->job
            // $event->exception

            \Log::info('failedjob', ['log' => $event]);

            // Get the Task ID
            if ($event->taskid) {
                $task = PhoneMACD::find($event->taskid);
                $task->fill(['updated_by' => 'Telecom Management Server', 'status' => 'failed', 'json' => $event->exception]);
                $task->save();
            }
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
