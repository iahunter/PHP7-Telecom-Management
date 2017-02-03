<?php

namespace App\Console\Commands\UnityConnection;

use App\Cupi;
use Illuminate\Console\Command;
use App\Http\Controllers\Cucmphone;

class UnityConnMB extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'unityconnection:import-ldapuser-mailboxes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Unity Mailboxes from LDAP';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        // Construct new cucmphone controller to call function. May move this to a model later.
        $this->cucmphone = new Cucmphone();

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */

    // Do you want to override the extension number of the mailbox if it is already set? If so change $override to true
    public $override = false;

    /*********************************************************
    * Set the User Template to be used for import.
    *
    * Change this every time you import a different Site!!!
    *********************************************************/
    public $user_template;

    public function handle()
    {

        // Paste in your users into this file from excel to be parsed and imported.
        require __DIR__.'/../CallManager/Imports/Phones.txt';

        $this->user_template = $user_template;
        $phones = $this->cucmphone->phones_string_to_array($phones);

        //print_r($phones);

        $usersarray = [];
        //$alias = "travis.riesenberg";
        foreach ($phones as $phone) {
            //print_r($phone);

            if (($phone['voicemail'] != 'Y') && ($phone['voicemail'] != 'Yes')) {
                echo "No Voicemail for {$phone['username']}...".PHP_EOL;
                continue;
            }

            $USERNAME = $phone['username'];
            $DN = $phone['dn'];
            $TEMPLATE = $this->user_template;
            $OVERRIDE = 'true';

            $userarray = Cupi::importLDAPUser($USERNAME, $DN, $TEMPLATE, $OVERRIDE = '');

            $usersarray[] = $userarray;
        }

        print_r($usersarray);
    }
}
