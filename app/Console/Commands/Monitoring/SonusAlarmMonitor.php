<?php

namespace App\Console\Commands\Monitoring;

use Mail;
use App\Ping;
use App\Sonus5k;
use Carbon\Carbon;
use App\TelecomInfrastructure;
use Illuminate\Console\Command;

class SonusAlarmMonitor extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'monitoring:sonus_alarm_monitor';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check Sonus Current Alarms and send Email if needed';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public $SBCS;

    // Discard these Sonus alarms from alerting emails.
    public $ALARMDISCARDS = [
                                'System Policer Alarm Level: Minor, Policer Type Media, Previous Level No Alarm.',
                                'System Policer Alarm Level: Minor, Policer Type ACL, Previous Level No Alarm.',
                                'System Policer Alarm Level: Major, Policer Type Discard Rule, Previous Level No Alarm.',
                                'System Policer Alarm Level: Minor, Policer Type Rogue Media, Previous Level No Alarm.',
                                'System Policer Alarm Level: Major, Policer Type Rogue Media, Previous Level Minor Alarm.',
                            ];

    public function __construct()
    {
        parent::__construct();

        // Populate SBC list
        $this->SBCS = [
                        env('SONUS1'),
                        env('SONUS2'),
                        ];

        // Construct new cucm object
        $this->cucm = new \Iahunter\CallmanagerAXL\Callmanager(env('CALLMANAGER_URL'),
                                                    storage_path(env('CALLMANAGER_WSDL')),
                                                    env('CALLMANAGER_USER'),
                                                    env('CALLMANAGER_PASS')
                                                    );
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        foreach ($this->SBCS as $SBC) {
            $change = false;
            $device = TelecomInfrastructure::where('hostname', $SBC)->first();

            $json = $device->json;
            if (! isset($json['sonusalarms'])) {
                $json['sonusalarms'] = [];
                $device->json = $json;
            }

            //print_r($device->json);

            $alarms = Sonus5k::listactivealarms($SBC);
            //$alarms = null;

            if ($alarms) {
                // Alarms exist
                $current_alarms = [];
                // index our alarms array by id
                foreach ($alarms as $alarm) {
                    if (in_array($alarm['desc'], $this->ALARMDISCARDS)) {
                        continue;
                    }
                    $current_alarms[$alarm['alarmId']] = $alarm;
                }

                // compare our current db alarms to the new alarms array
                $diff = array_diff_key($current_alarms, $json['sonusalarms']);

                //print_r($diff);

                if ($diff) {
                    // update our database if there is a difference and send an email.
                    print_r($json['sonusalarms']);
                    print_r($current_alarms);
                    $json['sonusalarms'] = $current_alarms;
                    $change = 'Alarm';
                }
            } else {
                // No alarms exist
                // Check dababase to make sure there aren't any set. if there are then delete them and send an email with updates.
                if ($alarms == null) {
                    $alarms = [];
                }

                $diff = array_diff_key($json['sonusalarms'], $alarms);
                //print_r($diff);
                if ($diff) {

                    // update the database.
                    print_r($json['sonusalarms']);
                    print_r($alarms);

                    $json['sonusalarms'] = $alarms;
                    $change = 'Alarm Cleared';
                }
            }

            if ($change) {
                // If we had a change update send an update.
                $time = Carbon::now().PHP_EOL;
                echo $time;
                $data = [
                        'time'          => $time,
                        'host'          => $device,
                        'hostname'      => $SBC,
                        'alarms'        => $json['sonusalarms'],
                        'status'        => $change,
                        ];

                $this->sendemail($data);
                $this->send_text_to_oncall($data);
                $device->json = $json;
                $device->save();
            }
        }
    }

    public function sendemail($data)
    {
        // Send email to the Oncall when status changes occur.

        // The HTML View is in resources/views/sonusalarm.blade.php
        Mail::send(['html'=>'sonusalarm'], $data, function ($message) {
            $message->subject('Telecom Management Alert - Sonus SBC Alarm Update!')
                        //->from([env('MAIL_FROM_ADDRESS')])
                        ->to([env('ONCALL_EMAIL_TO'), env('ONCALL_EMAIL_TO')])
                        ->bcc([env('BACKUP_EMAIL_TO'), env('BACKUP_EMAIL_TO')]);
        });

        echo 'Email sent to '.env('ONCALL_EMAIL_TO').PHP_EOL;
        echo 'Email sent to '.env('BACKUP_EMAIL_TO').PHP_EOL;
    }

    public function send_text_to_oncall($data)
    {
        // Custom Oncall Number - Comment out if not needed.
        if (env('ONCALL_PHONE_NUMBER')) {

            // If we are able to resolve the oncall number to email then do so and send a text message to oncall.
            $oncall_text = $this->getoncallphonenumber(env('ONCALL_PHONE_NUMBER'));
            //print $oncall_text;
            if ($oncall_text) {

                // This is currently only setup as verizon phones. If it uses other carrier cusomtization is needed.
                $this->ONCALL_EMAIL = $oncall_text.'@vtext.com';

                //print $this->ONCALL_EMAIL;

                Mail::send(['html'=>'sonusalarm'], $data, function ($message) {
                    $message->subject('Telecom Management Alert - Sonus SBC Alarm Update!')
                        ->to($this->ONCALL_EMAIL);
                    //->bcc([env('BACKUP_EMAIL_TO'), env('BACKUP_EMAIL_TO')]);
                });

                echo 'Email sent to '.$this->ONCALL_EMAIL.''.PHP_EOL;
            }
        }
    }

    public function getoncallphonenumber($DN)
    {

        // this function gets the callforward all from the Oncall number from CUCM. This is optional.
        $number = $this->cucm->get_object_type_by_pattern_and_partition($DN, 'Global-All-Lines', 'Line');
        $forward_number = $number['callForwardAll']['destination'];
        $pattern = '/^\+/';
        $replacement = '';
        $forward_number = preg_replace($pattern, $replacement, $forward_number);

        $pattern = '/^1+/';
        $replacement = '';
        $forward_number = preg_replace($pattern, $replacement, $forward_number);

        //print_r($forward_number);

        return $forward_number;
    }
}
