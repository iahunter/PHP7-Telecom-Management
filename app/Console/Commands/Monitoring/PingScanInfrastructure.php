<?php

namespace App\Console\Commands\Monitoring;

use Mail;
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
                $friendly_status = 'up';
            } else {
                $device_status = false;
                $friendly_status = 'down';
            }

             // Find record by id
            $device = TelecomInfrastructure::find($id);

            // If the Status change does not match what is currently set in the Database. Update the Database with the new status.
            if ($device->ip_reachable != $device_status) {
                $device->ip_reachable = $device_status;

                $time = Carbon::now().PHP_EOL;
                $hostdata = $hostname.' | '.$ip.PHP_EOL;
                $change = 'Status Change To: '.$friendly_status.PHP_EOL;

                echo $time.$hostdata.$change;

                $data = [
                        'time'        => $time,
                        'host'        => $host,
                        'status'      => $friendly_status,
                        ];

                Mail::send(['html'=>'email'], $data, function ($message) {
                    $message->subject('Telecom Management Alert - Device Status Change!')
                                ->from([env('MAIL_FROM_ADDRESS')])
                                ->to([env('ONCALL_EMAIL_TO'), env('ONCALL_EMAIL_TO')])
                                ->bcc([env('BACKUP_EMAIL_TO'), env('BACKUP_EMAIL_TO')]);
                });

                echo 'Email sent to '.env('ONCALL_EMAIL_TO').PHP_EOL;
                echo 'Email sent to '.env('BACKUP_EMAIL_TO').PHP_EOL;

                $device->updated_by = 'Monitoring Change';
                $device->save();
            }
        }
    }
}
