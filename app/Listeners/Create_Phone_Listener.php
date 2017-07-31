<?php

namespace App\Listeners;

use App\Events\Create_Phone_Event;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class Create_Phone_Listener
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
     * @param  Create_Phone_Event  $event
     * @return void
     */
    public function handle(Create_Phone_Event $event)
    {
        // Testing Event Listeners
        \Log::info('createPhoneListener', ['data' => $event->phone]);
    }
}
