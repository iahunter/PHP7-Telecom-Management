<?php

namespace App\Console\Commands\West911Enable;

use App\Cucmphoneconfigs;
use App\West911EnableEGW;
use Illuminate\Console\Command;

class PhoneEGWScanUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'west911enable:update_db_phonetable_ip_and_erl';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Phone ERL and IP Info from EGW and Write to Database';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->egw_endpoints = [];
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        echo 'Starting update_db_phonetable_ip_and_erl...'.PHP_EOL;
        // Update Table with IP and ERL info
        $this->egw_endpoints = West911EnableEGW::get_all_endpoints_ip_erl();
        if (! count($this->egw_endpoints)) {
            //print "No Endpoints...".PHP_EOL;
            //die();
        }

        //print_r($this->egw_endpoints);

        //$result = West911EnableEGW::get();
        //print "Starting chunking...".PHP_EOL;

        Cucmphoneconfigs::chunk(1000, function ($phones) {
            foreach ($phones as $phone) {
                $erl = '';
                $ipv4address = '';
                //print "Looking for Phone: {$phone->name}".PHP_EOL;
                if (array_key_exists($phone->name, $this->egw_endpoints)) {
                    //print "Found {$phone->name} in Endpoints...".PHP_EOL;
                    //print_r($this->egw_endpoints[$phone->name]);
                    $erl = $this->egw_endpoints[$phone->name]['erl'];
                    $ipv4address = $this->egw_endpoints[$phone->name]['ip_address'];
                    //print $erl.PHP_EOL;
                    //print $ipv4address.PHP_EOL;

                    if ($erl) {
                        $phone->fill(['erl' => $erl]);
                    }
                    if ($ipv4address) {
                        $phone->fill(['ipv4address' => $ipv4address]);
                    }

                    $phone->save();
                } else {
                    $phone->fill(['erl' => null]);
                    $phone->fill(['ipv4address' => null]);
                    $phone->save();
                }
            }
            //die();
        });

        echo 'Completed update_db_phonetable_ip_and_erl...'.PHP_EOL;
    }
}
