<?php

namespace App\Console\Commands\Sonus;

use Carbon\Carbon;
use Illuminate\Console\Command;

class GetSonusConfig extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sonus:getconfig';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get Sonus Config from CLI';

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
        $sbcs = [env('SONUS1'), env('SONUS2')];
        $svn = env('SONUS_SVN');
		
		$sbcs = array_filter($sbcs); 
		// print_r($this->SBCS); 
		
		if(!count($sbcs)){
			print "No SBCs Configured. Killing job.".PHP_EOL; 
			return; 
		}

        // Foreach SBC go get config and save it in our SVN Repo directory - This will be commited by Cron
        foreach ($sbcs as $sbc) {
            if (env('SONUS_DOMAIN_NAME')) {
                $hostname = $sbc.'.'.env('SONUS_DOMAIN_NAME');
            } else {
                $hostname = $sbc;
            }

            $params = [
                        'host'     => $hostname,
                        'username' => env('SONUSUSER'),
                        'password' => env('SONUSPASS'),
                        ];
            // Try to connect and run some commands
            try {
                $time = \Carbon\Carbon::now();

                echo "{$time} - Starting Sonus Config Script for {$sbc}".PHP_EOL;
                $ssh = new \Metaclassing\SSH($params);
                // for extreme debugging:
                //$ssh->loglevel = 9;
                // OOOooohhhh chainable ;D
                echo "Connecting to {$sbc}...".PHP_EOL;
                $ssh->connect()->exec('term len 0');
                echo "Connected to {$sbc}...".PHP_EOL;
                // to collect output as a string
                $ssh->timeout = 300;
                $command = 'show configuration | display set | nomore';
                echo "Executing '{$command}' ...".PHP_EOL;
                $output = $ssh->exec($command);

                echo 'Got Config... Trimming Lines not needed...'.PHP_EOL;

                // Trim unneeded lines
                $array = explode("\n", $output);

                array_shift($array); // Removing the first line
                array_pop($array); // Removing last line
                array_pop($array); // Removing last line

                $output = implode("\n", $array);

                echo 'Got Config... Saving to file...'.PHP_EOL;

                file_put_contents(storage_path("sonus/{$svn}/{$sbc}"), $output);

                $time = \Carbon\Carbon::now();

                echo "{$time} - Saved {$sbc} config".PHP_EOL.PHP_EOL;
            } catch (\Exception $e) {
                echo 'Encountered exception: '.$e->getMessage().PHP_EOL;
            }
        }
    }
}
