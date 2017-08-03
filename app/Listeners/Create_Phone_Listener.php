<?php

namespace App\Listeners;

use App\Cucmclass;
use App\Events\Create_Phone_Event;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Http\Controllers\Cucmphone as Cucmphone;

class Create_Phone_Listener implements ShouldQueue
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
     * @param  Create_Phone_Event  $event
     * @return void
     */
    public function handle(Create_Phone_Event $event)
    {
        // Testing Event Listeners
        \Log::info('createPhoneListener', ['data' => $event->phone]);

        $SITE = $event->phone['sitecode'];
        $DEVICE = $event->phone['device'];
        $NAME = $event->phone['name'];
        $FIRSTNAME = $event->phone['firstname'];
        $LASTNAME = $event->phone['lastname'];
        $USERNAME = $event->phone['username'];
        $DN = $event->phone['dn'];
        $EXTENSIONLENGTH = $event->phone['extlength'];
        $LANGUAGE = $event->phone['language'];
        $VOICEMAIL = $event->phone['voicemail'];
		
		$CREATEDBY = $event->phone['created_by'];
		
		$LOG = Cucmclass::provision_cucm_phone_axl(
                                                $SITE,
                                                $DEVICE,
                                                $NAME,
                                                $FIRSTNAME,
                                                $LASTNAME,
                                                $USERNAME,
                                                $DN,
                                                $EXTENSIONLENGTH,
                                                $LANGUAGE,
                                                $VOICEMAIL
                                            );

        \Log::info('createPhoneListener', ['created_by' => $CREATEDBY, 'phone' => $LOG]);
    }
}
