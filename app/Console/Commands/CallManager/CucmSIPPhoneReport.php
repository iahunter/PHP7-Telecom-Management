<?php

namespace App\Console\Commands\CallManager;

use App\Cucmphoneconfigs;
use Carbon\Carbon;
use DB;
use Illuminate\Console\Command;

class CucmSIPPhoneReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'callmanager:sipphone_report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Search CUCM SIP Phones from DB and print out csv report with name, owner, digestUser, description, devicepool, and model';

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
        $search = '%sip%';

        $count = Cucmphoneconfigs::where('model', 'LIKE', $search)->count();
        //print $count;
        if ($count) {
            $phones = Cucmphoneconfigs::where('model', 'LIKE', $search)->get();

            //print_r($phones);

            foreach ($phones as $phone) {
                echo $phone['name'].','.$phone['ownerid'].','.$phone['config']['digestUser'].','.$phone['description'].','.$phone['devicepool'].','.$phone['model'].PHP_EOL;
            }
        }
    }
}
