<?php

namespace App\Listeners;

use App\Events\Create_AD_IPPhone_Event;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class Create_AD_IPPhone_Listener
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
     * @param  Create_AD_IPPhone_Event  $event
     * @return void
     */
    public function handle(Create_AD_IPPhone_Event $event)
    {
        //
    }
}
