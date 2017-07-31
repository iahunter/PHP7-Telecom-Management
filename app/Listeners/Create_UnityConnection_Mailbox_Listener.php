<?php

namespace App\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Events\Create_UnityConnection_Mailbox_Event;

class Create_UnityConnection_Mailbox_Listener
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
        //
    }
}
