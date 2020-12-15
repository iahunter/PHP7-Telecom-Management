<?php

namespace App\Console\Commands\CallManager;

use App\CucmPhoneStats;
use App\Elastic\ElasticApiClient;
use Carbon\Carbon;
use Illuminate\Console\Command;

class FeedELKOldData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cucm:feedoldcucmphonestatstoelastic';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Feed Old data from phonestats database to Elastic';

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
        echo "Starting - {$this->signature} - {$start}".PHP_EOL;

        if (env('ELASTIC_URL') && env('ELASTIC_USER') && env('ELASTIC_PASS')) {
            $elasticUrl = env('ELASTIC_URL');

            $elasticUser = env('ELASTIC_USER');
            $elasticPassword = env('ELASTIC_PASS');

            echo $elasticUrl.PHP_EOL;

            $elastic = new ElasticApiClient($elasticUrl, $elasticUser, $elasticPassword);
        } else {
            echo 'No Elastic URL to write data'.PHP_EOL;

            return;
        }

        $stats = CucmPhoneStats::all();

        //print_r($stats);

        $total = count($stats);
        $count = 0;

        foreach ($stats as $stat) {
            $count++;
            echo "Starting {$count} of {$total}".PHP_EOL;

            $time = $stat['created_at'];
            $string = $time->toISOString();

            $INSERT = [];
            $INSERT = ['category' 		=> 'voice',
                'type'			           => 'cisco_phone_report',
                'total' 		          => $stat['total'],
                'registered'	       => $stat['registered'],
                'stats'			          => $stat['stats'],
                'timestamp' 	       => $string,
            ];

            //print_r($INSERT);

            $json = json_encode($INSERT);

            $elastic->postNetworkData($json);
        }

        $end = \Carbon\Carbon::now();
        echo "Started at: {$start}".PHP_EOL;
        echo "Completed at {$end}".PHP_EOL;

        echo "Completed - {$this->signature} - {$end}".PHP_EOL;
    }
}
