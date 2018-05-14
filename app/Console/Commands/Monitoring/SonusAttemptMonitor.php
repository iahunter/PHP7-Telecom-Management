<?php

namespace App\Console\Commands\Monitoring;

use DB;
use Mail;
use App\Ping;
use App\Sonus5k;
use Carbon\Carbon;
use App\Sonus5kCDR;
use App\TelecomInfrastructure;
use Illuminate\Console\Command;

class SonusAttemptMonitor extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'monitoring:sonus_cdr_attempt_monitor';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check CDRs from Last Hour and Alert on Attempt Thresholds';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public $THRESHOLD_PERCENT = 30;

    public $ONCALL_EMAIL;

    // Discard these Sonus alarms from alerting emails.
    public $DISCARD_ATTEMPT_TYPES = [
                                    '16 - NORMAL ROUTE CLEARING',
                                    '1 - UNALLOCATED NUMBER',
                                    '17 - USER BUSY',
                                    ];

    public function __construct()
    {
        parent::__construct();
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
        $thresholds = [];
        $stats = $this->list_todays_attempts_summary_report();
        //print_r($stats);
        foreach ($stats as $key => $value) {
            $time = $key;
            //print PHP_EOL;
            //print_r($value);

            foreach ($value as $key => $stat) {
                if ($key == 'totalCalls') {
                    $totalcalls = $stat;
                    //print "Total: ".$totalcalls.PHP_EOL;
                    continue;
                }
                if (in_array($key, $this->DISCARD_ATTEMPT_TYPES)) {
                    // We dont care about these so don't alert on them.
                    continue;
                }

                //print $key." | ".$stat. " | ";

                $percentage = round($stat / $totalcalls * 100, 0);

                //print $percentage." %".PHP_EOL;

                // Only alert if total calls is greater than 500.
                if ($totalcalls > 500) {
                    if ($percentage > $this->THRESHOLD_PERCENT) {
                        $thresholds[$key] = [];
                        $thresholds[$key]['percentage'] = $percentage;
                        $thresholds[$key]['calls'] = $stat;
                    }
                }
            }
        }

        $count = count($thresholds);

        $top_numbers = $this->list_last_hour_top_attempt_counts_by_called_number_report();

        $data = [
                    'time'                         => $time,
                    'alarms_count'                 => $count,
                    'thresholds'                   => $thresholds,
                    'configured_threshold'         => $this->THRESHOLD_PERCENT,
                    'stats'                        => $stats,
                    'top_numbers'				              => $top_numbers,
                    ];

        print_r($data);

        if ($count) {
            // If we have some alerts that have met the threshold, send an email alert.
            print_r($data);
            $this->sendemail($data);
            //$this->send_text_to_oncall($data);
        }
    }

    public function sendemail($data)
    {
        // Send email to the Oncall threshold met.

        // The HTML View is in resources/views/sonuscdralarm.blade.php
        Mail::send(['html'=>'sonuscdralarm'], $data, function ($message) {
            $message->subject('Telecom Management Alert - Sonus SBC Attempt Threshold Alert!')
                        //->from([env('MAIL_FROM_ADDRESS')])
                        ->to([env('ONCALL_EMAIL_TO')])
                        ->bcc([env('BACKUP_EMAIL_TO')]);
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

                Mail::send(['html'=>'sonuscdralarm'], $data, function ($message) {
                    $message->subject('Telecom Management Alert - Sonus SBC Attempt Threshold Alert!')
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

    public function list_last_hour_top_attempt_counts_by_called_number_report()
    {
        // Get Sonus SBC top 10 Attempt Counts by Called Number.
        $cdrs = Sonus5kCDR::list_last_hour_top_attempt_counts_by_called_number_report();

        $cdrs_array = json_decode(json_encode($cdrs), true);

        return $cdrs_array;
    }

    public function list_todays_attempts_summary_report()
    {
        $return = [];

        $hours = 1;

        $now = Carbon::now()->setTimezone('UTC');
        $start = $now->subHours($hours);
        $end = Carbon::now()->setTimezone('UTC');

        // Get all the active attempt disconnet reasons in use in last 24s.
        $codes = \App\Sonus5kCDR::groupBy('disconnect_reason')
                ->select('disconnect_reason', DB::raw('count(*) as total'))
                ->whereBetween('start_time', [$start, $end])
                ->where('type', 'ATTEMPT')
                ->get();

        $x = 0;

        // get all the records for every hour in for the specified number of hours.
        while ($x != $hours) {
            $now = Carbon::now()->setTimezone('UTC');
            $starthour = $now->subHours($hours);
            $copystart = clone $starthour;
            $subhour = $copystart->addHours(1)->toDateTimeString();
            $starthour = $starthour->toDateTimeString();
            $start = $starthour;
            $end = $subhour;
            $hours--; // Subtract an hour from hours when looping.

            $calls = \App\Sonus5kCDR::groupBy('disconnect_reason')
                ->select('disconnect_reason', DB::raw('count(*) as total'))
                ->whereBetween('start_time', [$start, $end])
                ->where('type', 'ATTEMPT')
                ->get();

            $totalcalls = \App\Sonus5kCDR::whereBetween('start_time', [$start, $end])->count();

            $pktlosscalls = Sonus5kCDR::whereBetween('start_time', [$start, $end])
                                ->where(function ($query) {
                                    $query->where('ingress_lost_ptks', '>', 100)
                                    ->orWhere('egress_lost_ptks', '>', 100);
                                })
                                ->get();
            $losscalls = [];

            foreach ($pktlosscalls as $call) {
                //$losscalls = [];

                /*
                if ($call['call_duration']) {
                    $call['call_duration'] = gmdate('H:i:s', ($call['call_duration'] * 10) / 1000);
                }

                //$call['disconnect_initiator_desc'] = Sonus5kCDR::get_disconnect_initiator_code($call['disconnect_initiator']);
                //$call['disconnect_reason_desc'] = Sonus5kCDR::get_call_termination_code($call['disconnect_reason']);
                */

                $ingress_pkt_loss = $call['cdr_json']['Ingress Number of Packets Recorded as Lost'];
                $ingress_pkts_recieved = $call['cdr_json']['Ingress Number of Audio Packets Received'];
                $ingress_pkt_loss_percent = $ingress_pkt_loss / ($ingress_pkts_recieved + $ingress_pkt_loss) * 100;
                $ingress_pkt_loss_percent = round($ingress_pkt_loss_percent, 2, PHP_ROUND_HALF_UP);
                $call['ingress_pkt_loss_percent'] = $ingress_pkt_loss_percent;

                $egress_pkt_loss = $call['cdr_json']['Egress Number of Packets Recorded as Lost'];
                $egress_pkts_recieved = $call['cdr_json']['Egress Number of Audio Packets Received'];
                $egress_pkt_loss_percent = $egress_pkt_loss / ($egress_pkts_recieved + $egress_pkt_loss) * 100;
                $egress_pkt_loss_percent = round($egress_pkt_loss_percent, 2, PHP_ROUND_HALF_UP);
                $call['egress_pkt_loss_percent'] = $egress_pkt_loss_percent;

                //return $call;
                if ($ingress_pkt_loss_percent > 1 || $egress_pkt_loss_percent > 1) {
                    $losscalls[] = $call;
                }
            }

            $pktlosscalls = array_reverse($losscalls);
            $pktlosscalls = count($pktlosscalls);

            $codes_inuse = [];

            foreach ($codes as $code) {
                // Resolve the code to description.
                $code = $code['disconnect_reason'].' - '.Sonus5kCDR::get_call_termination_code($code['disconnect_reason']);

                // Set default value of 0 for all inuse code for each interval.
                $codes_inuse['totalCalls'] = 0;
                $codes_inuse['packetLoss'] = 0;
                $codes_inuse[$code] = 0;
            }

            $attempt_count = $codes_inuse;

            // set the value for each disconnect type in time window.
            foreach ($calls as $i) {
                $attempt_count['totalCalls'] = $totalcalls;
                $attempt_count['packetLoss'] = $pktlosscalls;
                $attempt_count[$i->disconnect_reason.' - '.Sonus5kCDR::get_call_termination_code($i->disconnect_reason)] = $i->total;
            }

            // Append to the return array with the end time as the key.
            $return[$end] = $attempt_count;
        }

        return $return;
    }
}
