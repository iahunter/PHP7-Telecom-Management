<?php

namespace App\Console\Commands\Monitoring;

use App\Ping;
use Carbon\Carbon;
use App\TelecomInfrastructure;
use Illuminate\Console\Command;

class PingScanInfrastructure extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'monitoring:pingscan_infrastructure';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ping Scan Infrastructure and Update DB on Status of Device';

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
        $hosts = TelecomInfrastructure::all();

        foreach ($hosts as $host) {
            $hostname = $host['hostname'];
            $id = $host['id'];
            $ip = $host['ip_address'];
            $host_status = Ping::pinghost($ip);
            if ($host_status['result'] == 'echo reply') {
                $device_status = true;
            } else {
                $device_status = false;
            }

             // Find record by id
            $device = TelecomInfrastructure::find($id);

            // If the Status change does not match what is currently set in the Database. Update the Database with the new status.
            if ($device->ip_reachable != $device_status) {
                $device->ip_reachable = $device_status;
                $device->updated_by = 'Monitoring Change';
                $device->save();
                echo Carbon::now().PHP_EOL;
                echo $hostname.' | '.$ip.PHP_EOL;
                echo 'Status Change: '.$host_status['result'].PHP_EOL;
            }
        }
    }
}
