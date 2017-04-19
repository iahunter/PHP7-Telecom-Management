<?php

namespace App\Console\Commands\Sonus;


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
		
		// Foreach SBC go get config and save it in our SVN Repo directory - This will be commited by Cron
		foreach($sbcs as $sbc){
			$params = [
				'host'     => $sbc,
				'username' => env('SONUSUSER'),
				'password' => env('SONUSPASS'),
			  ];
			// Try to connect and run some commands
			try {
				echo 'Starting Sonus Config Script'.PHP_EOL;
				$ssh = new \Metaclassing\SSH($params);
				// for extreme debugging:
				//$ssh->loglevel = 9;
				// OOOooohhhh chainable ;D
				echo "Connecting to {$sbc}...".PHP_EOL;
				$ssh->connect()->exec('term len 0');
				echo 'Connected...'.PHP_EOL;
				// to collect output as a string
				$ssh->timeout = 300;
				$command = 'show configuration | display set | nomore';
				echo 'Executing "show configuration | display set | nomore" ...'.PHP_EOL;
				$output = $ssh->exec($command);
				echo 'Got Config... Saving to file...'.PHP_EOL;
				
				//file_put_contents("storage/sonus/{$sbc}", $output);
				
				file_put_contents("storage/sonus/{$svn}/{$sbc}", $output);
				//file_put_contents('storage/sonus/sonus.config', $output);
				echo 'Saved sonus config'.PHP_EOL;
			} catch (\Exception $e) {
				echo 'Encountered exception: '.$e->getMessage().PHP_EOL;
			}
		}

    }
}
