<?php

namespace App\Console\Commands\CallManager;

use App\Cucmphoneconfigs;
use App\CucmRealTime;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CucmPhoneIPAddresses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'callmanager:phone_ip_address_and_status_scan';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get Phone IPs and Registration status';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->CucmRealTime = new CucmRealTime();

        parent::__construct();
    }

    public $count;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $start = Carbon::now();

        // Go thru all phone records 1000 per chunk and update our table ip address and registration status from cucm.

        // This will take around 1 minute per 1000k phones.

        Cucmphoneconfigs::chunk(1000, function ($phones) {
            $query = [];

            foreach ($phones as $phone) {
                $query[] = $phone->name;
            }

            if (count($query)) {
            }
            $risdb_phones = $this->get_phone_ip($query);

            foreach ($phones as $phone) {
                $this->count++;

                //print_r($phone);
                $risdb_registration_status = '';
                $risdb_ipv4address = '';

                echo "{$this->count}: Looking for Phone: {$phone->name}".PHP_EOL;
                //if (array_key_exists($phone->name, $this->egw_endpoints)) {

                if (array_key_exists($phone->name, $risdb_phones)) {
                    echo "Found {$phone->name} in Risdb Phones...".PHP_EOL;
                    //print_r($this->egw_endpoints[$phone->name]);
                    $risdb_registration_status = $risdb_phones[$phone->name]['status'];
                    $risdb_ipv4address = $risdb_phones[$phone->name]['ipAddress'];
                    echo $risdb_registration_status.PHP_EOL;
                    echo $risdb_ipv4address.PHP_EOL;

                    if ($risdb_registration_status) {
                        $phone->fill(['risdb_registration_status' => $risdb_registration_status]);
                    }
                    if ($risdb_ipv4address) {
                        $phone->fill(['risdb_ipv4address' => $risdb_ipv4address]);
                    }

                    $phone->save();
                } else {
                    $phone->fill(['risdb_registration_status' => null]);
                    $phone->fill(['risdb_ipv4address' => null]);
                    $phone->save();
                }
            }

            // Sleep for 60 seconds due to 15 query per minute limitation on CUCM in RISDB API. There are other systems using this so we don't want to bog them down.
            echo 'Sleeping for 60 seconds...'.PHP_EOL;
            sleep(60);
            //die();
        });

        $end = Carbon::now();

        echo 'Start: '.$start;
        echo 'End: '.$end;
    }

    public function get_phone_ip($phones)
    {
        // CUCM Limitation for RISDB querie in version 10.5.. i have heard its less in earlier versions but we do not support them.
        $MAX_PHONE_QUERY = 1000;

        //$phones = ['SEP00DA5527F681', 'SEP0C6803C0AFC1', 'SEP000000000000'];

        $count = 0;

        $result = [];
        foreach ($phones as $phone) {
            $searchCriteria["SelectItem[{$count}]"]['Item'] = $phone;
            $count++;

            if ($count >= $MAX_PHONE_QUERY || $count >= count($phones)) {
                $query_result = $this->CucmRealTime->getIPAddresses($searchCriteria);
                $result = array_merge($result, $query_result);
                $count = 0;

                // May need to sleep if the query is to large due to the RISDB limitation of 15 queries per minute.
                // I added this into the handle to account for this.
                //sleep(15);
            }
        }

        //print_r($result);
        //echo count($result).PHP_EOL;

        return $result;
    }
}
