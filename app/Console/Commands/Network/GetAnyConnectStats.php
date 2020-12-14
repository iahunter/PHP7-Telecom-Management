<?php

namespace App\Console\Commands\Network;

use App\Elastic\ElasticApiClient;
use Illuminate\Console\Command;

class GetAnyConnectStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'network:get_asa_cluster_stats';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get ASA VPN Stats from all members of ASA Cluster';

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
        $start = \Carbon\Carbon::now();
        echo "Started at: {$start}".PHP_EOL;

        if (env('ASA_ARRAY')) {
            $asas = json_decode(env('ASA_ARRAY'), true);
            print_r($asas);
        } else {
            echo 'No ASAs found in .env file. Ending Command Tasks'.PHP_EOL;

            return;
        }

        $stats = [];
        $json = [];

        // Foreach gateway get active calls.
        $total_active_sessions = 0;
        $total_capacity = 0;

        foreach ($asas as $name => $ip) {
            echo 'Starting: '.$name.PHP_EOL;

            $params = [
                'host'     => $ip,
                'username' => env('LDAP_USER'),
                'password' => env('LDAP_PASS'),
            ];

            // Try to connect and run some commands
            try {
                $ssh = new \Metaclassing\SSH($params);

                echo "Connecting to {$name}...".PHP_EOL;
                $e = $ssh->connect()->exec("enable\n".env('LDAP_PASS'));			// enable and password login
                $ssh->exec('term pager 0');											// Change terminal length
                //echo "Connected to {$name}...".PHP_EOL;

                $ssh->timeout = 100;

                $command = 'show vpn-sessiondb';									// Get VPN Session Info from ASA
                $output = $ssh->exec($command);

                $stats[$name] = [];  												// Create new data array for device stats.

                preg_match("/Total Active and Inactive\s+:\s+(\d+)/", $output, $vpn_sessions); 	// searches for Leading text followed by spaces followed by : followd by spaces and then digits.
                preg_match("/Device Total VPN Capacity\s+:\s+(\d+)/", $output, $vpn_capacity); 	// searches for Leading text followed by spaces followed by : followd by spaces and then digits.
                preg_match("/Device Load\s+:\s+(\d+%)/", $output, $device_load); 				// searches for Leading text followed by spaces followed by : followd by spaces and then digits.

                $total_active_sessions = $total_active_sessions + $vpn_sessions[1];
                $total_capacity = $total_capacity + $vpn_capacity[1];

                $stats[$name]['vpn_sessions'] = $vpn_sessions[1];
                $stats[$name]['vpn_capacity'] = $vpn_capacity[1];
                $stats[$name]['device_load'] = $device_load[1];

                $json[$name] = [];
                $json[$name]['raw'] = [];
                $json[$name]['raw'][$command] = $output; 							// Store all raw output in raw

                //print_r($data);
                //die();
            } catch (\Exception $e) {
                echo 'Encountered exception: '.$e->getMessage().PHP_EOL;
                continue;
            }
        }

        // Build Array to Insert
        $INSERT = ['category' 	=> 'network',
            'type'	   	        => 'vpn_report',
            'total' 	          => $total_active_sessions,
            'int0'		           => $total_capacity,
            'stats'		          => $stats,
            'json'		           => $json,
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
    }
}
