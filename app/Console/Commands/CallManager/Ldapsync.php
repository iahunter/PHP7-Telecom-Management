<?php

namespace App\Console\Commands\CallManager;

use Illuminate\Console\Command;

class Ldapsync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'callmanager:ldapsync-start';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kicks off the ldap sync process in CUCM to Import users from the LDAP Directory';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        // Construct new cucm object
        $this->cucm = new \Iahunter\CallmanagerAXL\Callmanager(env('CALLMANAGER_URL'),
                                                    storage_path(env('CALLMANAGER_WSDL')),
                                                    env('CALLMANAGER_USER'),
                                                    env('CALLMANAGER_PASS')
                                                    );

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            $ldapsync = $this->cucm->do_ldap_sync(env('CALLMANAGER_LDAP_NAME'), 'true');

            echo $ldapsync->return.PHP_EOL;
        } catch (\Exception $e) {
            echo 'Callmanager blew uP: '.$e->getMessage().PHP_EOL;
        }
    }
}
