<?php

namespace App\Console\Commands\Sonus;

use Carbon\Carbon;
use Illuminate\Console\Command;

class CleanSonusLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sonus:log_cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleanup the log directory';

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
        foreach ($sbcs as $sbc) {
            if (env('SONUS_DOMAIN_NAME')) {
                $hostname = $sbc.'.'.env('SONUS_DOMAIN_NAME');
            } else {
                $hostname = $sbc;
            }

            $params = [
                        'host'     => $hostname,
                        'username' => env('SONUSSSHUSER'),
                        'password' => env('SONUSSSHPASS'),
                        ];
            // Try to connect and run some commands
            try {
                $time = \Carbon\Carbon::now();

                echo "{$time} - Starting Sonus Log Cleanup for {$sbc}".PHP_EOL;
                $ssh = new \phpseclib\Net\SSH2($hostname, 2024);

                $connected = $ssh->login(env('SONUSSSHUSER'), env('SONUSSSHPASS'));

                if (! $connected) {
                    echo 'Connection Failed...';
                } else {
                    echo "Connected to {$sbc}...".PHP_EOL;
                }

                echo "Connecting to {$sbc}...".PHP_EOL;

                // Needed to edit the visudo file in order to allow deletes without sudo pass.

                // Get and print the files exist that are older than 30 days
                echo 'List of ACT Logs older than 30 days.'.PHP_EOL;
                $command = "find /var/log/sonus/sbx/evlog/ -type f -mtime +30 -name '*.ACT'";
                $output = $ssh->exec($command);
                echo $output;

                // Delete the files older than 30 days
                $command = "sudo find /var/log/sonus/sbx/evlog/ -type f -mtime +30 -name '*.ACT' -execdir rm -- '{}' \;";
                $output = $ssh->exec($command);
                echo $output;

                // Get and print the files exist that are older than 30 days
                echo 'List of DBG Logs older than 30 days.'.PHP_EOL;
                $command = "find /var/log/sonus/sbx/evlog/ -type f -mtime +30 -name '*.DBG'";
                $output = $ssh->exec($command);
                echo $output;

                // Delete the files older than 30 days
                $command = "sudo find /var/log/sonus/sbx/evlog/ -type f -mtime +30 -name '*.DBG' -execdir rm -- '{}' \;";
                $output = $ssh->exec($command);
                echo $output;

                $time = \Carbon\Carbon::now();

                echo "{$time} - Completed Sonus Log Cleanup for {$sbc}".PHP_EOL;
            } catch (\Exception $e) {
                echo 'Encountered exception: '.$e->getMessage().PHP_EOL;
            }
        }
    }
}
