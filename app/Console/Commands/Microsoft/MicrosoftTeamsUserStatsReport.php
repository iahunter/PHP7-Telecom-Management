<?php

namespace App\Console\Commands\Microsoft;

use App\Did;
use App\Elastic\ElasticApiClient;
use Carbon\Carbon;
use Illuminate\Console\Command;

class MicrosoftTeamsUserStatsReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'microsoft:get_teams_user_stats_report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get Teams Voice User Stat Summary from Number Database';

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

        $count = DID::where('system_id', 'like', '%MicrosoftTeams%')
                ->count();

        $response = [
            'status_code'       => 200,
            'success'           => true,
            'message'           => '',
            'response'          => $count,
        ];

        // Build Array to Insert
        $INSERT = ['category' 	=> 'voice',
            'type'	   	        => 'microsoft_teams_user_count',
            'total' 	          => $count,
        ];

        //print_r($INSERT);

        \App\Reports::create($INSERT);      // Run in Cron every 10 mins and store stats in Reports Database

        $now = \Carbon\Carbon::now();

        $string = $now->toISOString();
        $INSERT['timestamp'] = $string;

        //print_r($INSERT);

        $json = json_encode($INSERT);

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

        echo "Completed - {$this->signature} - {$end}".PHP_EOL;
    }
}
