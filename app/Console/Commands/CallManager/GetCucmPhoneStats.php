<?php

namespace App\Console\Commands\CallManager;

use App\Cucmphoneconfigs;
use App\CucmPhoneStats;
use App\Elastic\ElasticApiClient;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GetCucmPhoneStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cisco_phone:get_phone_stats';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run Report that compiles phone counts and registration summary info and it inserts into the cucmphonestats table';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $start = Carbon::now();
        echo 'Starting - cisco_phone:get_phone_stats - '.$start.PHP_EOL;

        $total = Cucmphoneconfigs::get_active_phone_count();
        $registered = Cucmphoneconfigs::get_phone_registered_count();
        $stats = Cucmphoneconfigs::get_phone_registered_count_by_type();

        // Insert new Record
        //$record = CucmPhoneStats::create($insert);

        // Build Array to Insert
        $INSERT = ['category' 	=> 'voice',
            'type'	   	        => 'cisco_phone_report',
            'total' 	          => $registered,
            'int0'		           => $total,
            'stats'		          => $stats,
        ];

        //print_r($INSERT);

        \App\Reports::create($INSERT);      // Run in Cron every 10 mins and store stats in Reports Database

        $ELASTIC = ['category' 		 => 'voice',
            'type'	   	        	  => 'cisco_phone_report',
            'registered' 	        => $registered,
            'total'		           	 => $total,
            'stats'		          	  => $stats,
        ];

        $now = \Carbon\Carbon::now();

        $string = $now->toISOString();
        $ELASTIC['timestamp'] = $string;

        //print_r($ELASTIC);

        $json = json_encode($ELASTIC);

        echo $json.PHP_EOL;

        if (env('ELASTIC_URL') && env('ELASTIC_USER') && env('ELASTIC_PASS')) {
            $elasticUrl = env('ELASTIC_URL');

            $elasticUser = env('ELASTIC_USER');
            $elasticPassword = env('ELASTIC_PASS');

            echo $elasticUrl.PHP_EOL;
        } else {
            echo 'No Elastic URL to write data'.PHP_EOL;

            return;
        }

        $elastic = new ElasticApiClient($elasticUrl, $elasticUser, $elasticPassword);

        $elastic->postNetworkData($json);

        $end = \Carbon\Carbon::now();
        echo "Started at: {$start}".PHP_EOL;
        echo "Completed at {$end}".PHP_EOL;

        $end = Carbon::now();

        echo 'Completed - cisco_phone:get_phone_stats - '.$end.PHP_EOL;
    }
}
