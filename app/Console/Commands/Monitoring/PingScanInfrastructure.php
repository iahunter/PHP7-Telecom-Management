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
            $device = TelecomInfrastructure::find($host['id']);

            $hostname = $host['hostname'];
            $ip = $host['ip_address'];

            if (! $device->monitor) {
                //echo 'Unmonitored Device Found. Skipping: '.$hostname.' | '.$ip.PHP_EOL;
                continue;
            }

            $host_status = Ping::pinghost($ip);

            $i = 1;
            while ($host_status['result'] != 'echo reply' && $i < 5) {
                $i++;
                $host_status = Ping::pinghost($ip);
            }

            if ($host_status['result'] == 'echo reply') {
                $device_status = true;
                $friendly_status = 'up';
            } else {
                $device_status = false;
                $friendly_status = 'down';
            }

            if (! $device->json) {
                // Build Monitoring Ping Counter if it doesn't exist.
                $json = ['ping' => [
                                        'state'   => true,
                                        'counter' => 0,
                                    ],
                        ];

                $device->json = $json;

                $device->updated_by = 'Monitoring Change';
                $device->save();
            }

            $json = $device->json;

            // If the Status change does not match what is currently set in the Database. Update the Database with the new status.
            if ($device->ip_reachable != $device_status) {
                if ($device_status == true) {
                    if ($json['ping']['state'] != true) {
                        $json['ping']['state'] = true;
                        $json['ping']['counter'] = 1;
                    } else {
                        $json['ping']['state'] = true;
                        $json['ping']['counter'] = $json['ping']['counter'] + 1;
                    }
                    if ($json['ping']['counter'] >= 5) {
                        $json['ping']['counter'] = 0;
                        $device->ip_reachable = $device_status;
                    } else {
                        $device->json = $json;
                        $device->save();
                        continue;
                    }
                }
                if ($device_status == false) {
                    if ($json['ping']['state'] != false) {
                        $json['ping']['state'] = false;
                        $json['ping']['counter'] = 1;
                    } else {
                        $json['ping']['state'] = false;
                        $json['ping']['counter'] = $json['ping']['counter'] + 1;
                    }
                    if ($json['ping']['counter'] >= 5) {
                        $json['ping']['counter'] = 0;
                        $device->ip_reachable = $device_status;
                    } else {
                        $device->json = $json;
                        $device->save();
                        continue;
                    }
                }

                $device->json = $json;

                $time = Carbon::now().PHP_EOL;
                $hostdata = $hostname.' | '.$ip.PHP_EOL;
                $change = 'Status Change To: '.$friendly_status.PHP_EOL;

                echo $time.$hostdata.$change;

                $data = [
                        'time'        => $time,
                        'host'        => $host,
                        'status'      => $friendly_status,
                        ];

                $this->sendemail($data);

                $device->updated_by = 'Monitoring Change';
                $device->save();
            } elseif ($json['ping']['state'] != $device_status) {
                // Reset counters.
                $json['ping']['state'] = $device_status;
                $json['ping']['counter'] = 0;

                // Save to device.
                $device->json = $json;
                $device->save();
            }
        }
    }

    public function sendemail($data)
    {
        // Send email to the Oncall when status changes occur.
        Mail::send(['html'=>'email'], $data, function ($message) {
            $message->subject('Telecom Management Alert - Device Status Change!')
                        ->from([env('MAIL_FROM_ADDRESS')])
                        ->to([env('ONCALL_EMAIL_TO'), env('ONCALL_EMAIL_TO')])
                        ->bcc([env('BACKUP_EMAIL_TO'), env('BACKUP_EMAIL_TO')]);
        });

        echo 'Email sent to '.env('ONCALL_EMAIL_TO').PHP_EOL;
        echo 'Email sent to '.env('BACKUP_EMAIL_TO').PHP_EOL;
    }
}
