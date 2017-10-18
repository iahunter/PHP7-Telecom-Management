<?php

namespace App\Console\Commands\Callmanager;

use Carbon\Carbon;
use App\GatewayCalls;
use Illuminate\Console\Command;

class CucmGatewayCallCounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'callmanager:cucm-gateway-call-counts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get Active Call Counts from each Gateway via SSH';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        // Construct new cucm object
        $this->cucm = new \CallmanagerAXL\Callmanager(env('CALLMANAGER_URL'),
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
        $start = \Carbon\Carbon::now();

        try {
            $gateways = $this->cucm->get_object_type_by_site('%', 'H323Gateway');
        } catch (\Exception $e) {
            echo 'Encountered exception: '.$e->getMessage().PHP_EOL;
            die();
        }

        //$gateways = ['10.252.22.41'];

        $calls = [];

        $total = count($gateways);
        $count = 0;

        $total_calls = 0;

        if (! $total) {
            echo 'No Gateways Found';
            die();
        }
        echo "Found {$total} Gateways".PHP_EOL;

        // Foreach gateway get active calls.
        foreach ($gateways as $gateway) {
            $count++;
            echo "Starting {$count} of {$total}".PHP_EOL;

            $params = [
                        'host'     => $gateway,
                        'username' => env('LDAP_USER'),
                        'password' => env('LDAP_PASS'),
                        ];
            // Try to connect and run some commands
            try {
                $time = \Carbon\Carbon::now();
                echo "{$time} - Getting Active Calls on {$gateway}".PHP_EOL;
                $ssh = new \Metaclassing\SSH($params);
                // for extreme debugging:
                //$ssh->loglevel = 9;
                // OOOooohhhh chainable ;D
                echo "Connecting to {$gateway}...".PHP_EOL;
                $ssh->connect()->exec('term len 0');
                echo "Connected to {$gateway}...".PHP_EOL;
                // to collect output as a string
                $ssh->timeout = 50;
                $command = 'sh voice call status';
                echo "Executing '{$command}' ...".PHP_EOL;
                $output = $ssh->exec($command);

                // Trim unneeded lines
                $array = explode("\n", $output);

                print_r($array);

                $search = '/active call/';
                foreach ($array as $line) {
                    //print $line.PHP_EOL;
                    if (preg_match($search, $line)) {
                        echo 'Found!'.PHP_EOL;
                        echo $line;
                        $line = explode('active', $line);
                        print_r($line);
                        $call_count = trim($line[0]);
                        if ($call_count == 'No') {
                            $call_count = 0;
                        }
                        $total_calls = $total_calls + $call_count;
                        $calls[$gateway] = $call_count;
                    }
                }
            } catch (\Exception $e) {
                echo 'Encountered exception: '.$e->getMessage().PHP_EOL;
            }
        }
        //print "Finished";
        $calls['total'] = $total_calls;
        print_r($calls);

        $end = \Carbon\Carbon::now();
        echo "Started at: {$start}".PHP_EOL;
        echo "Completed at {$end}".PHP_EOL;

        \App\GatewayCalls::create(['totalCalls' => $calls['total'], 'stats' => $calls]);
    }
}
