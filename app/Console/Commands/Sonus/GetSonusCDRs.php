<?php

namespace App\Console\Commands\Sonus;

use Carbon\Carbon;
use App\Sonus5kCDR;
use Illuminate\Console\Command;
use phpseclib\Net\SFTP as Net_SFTP;

class GetSonusCDRs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sonus:write-cdrs-to-db';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch new CDRs from SBC and write to Database';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->SBCS = [
                        env('SONUS1'),
                        env('SONUS2'),
                        ];

        parent::__construct();
        // Populate SBC list
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        foreach ($this->SBCS as $SBC) {

            // Get latest CDR File.
            $locations = Sonus5kCDR::get_cdr_log_names($SBC);

            // Trim off the two most recent files and set them to the locations.
            // $locations = array_slice($locations, 0, 1);

            foreach ($locations as $location) {
                $cdrs = $this->get_cdrs_from_file($SBC, $location);
                $cdr_array = Sonus5kCDR::parse_cdr($cdrs);
                //print_r($cdr_array);
                foreach ($cdr_array as $cdr) {
                    $RECORD = [];
                    $RECORD['gw_name'] = $cdr['Gateway Name'];
                    $RECORD['type'] = $cdr['Record Type'];
                    $RECORD['accounting_id'] = $cdr['Accounting ID'];
                    $RECORD['gcid'] = $cdr['Global Call ID (GCID)'];

                    // Convert the Sonus record time to Carbon Y/m/d so we can sort by date and time easier.
                    //$RECORD['start_date'] = $cdr['Start Time (MM/DD/YYYY)'];
                    //$RECORD['start_time'] = $cdr['Start Time (HH/MM/SS.s)'];
                    $date = Sonus5kCDR::convert_sonus_date_format_to_carbon($cdr['Start Time (MM/DD/YYYY)']);
                    $RECORD['start_time'] = $date.' '.$cdr['Start Time (HH/MM/SS.s)'];

                    // Convert the Sonus record time to Carbon Y/m/d so we can sort by date and time easier.
                    //$RECORD['disconnect_date'] = $cdr['Disconnect Time (MM/DD/YYYY)'];
                    //$RECORD['disconnect_time'] = $cdr['Disconnect Time (HH:MM:SS.s)'];
                    //$date = Sonus5kCDR::convert_sonus_date_format_to_carbon($cdr['Disconnect Time (MM/DD/YYYY)']);
                    $date = Sonus5kCDR::convert_sonus_date_format_to_carbon($cdr['Disconnect Time (MM/DD/YYYY)']);
                    $RECORD['disconnect_time'] = $date.' '.$cdr['Disconnect Time (HH:MM:SS.s)'];

                    $RECORD['disconnect_initiator'] = $cdr['Disconnect Initiator'];
                    $RECORD['disconnect_reason'] = $cdr['Call Disconnect Reason'];

                    $RECORD['calling_name'] = $cdr['Calling Name'];
                    $RECORD['calling_number'] = $cdr['Calling Number'];
                    $RECORD['dialed_number'] = $cdr['Dialed Number'];
                    $RECORD['called_number'] = $cdr['Called Number'];

                    $RECORD['route_label'] = $cdr['Route Label'];
                    $RECORD['ingress_trunkgrp'] = $cdr['Ingress Trunk Group Name'];
                    $RECORD['ingress_media'] = $cdr['Ingress IP Circuit End Point'];

                    $RECORD['egress_trunkgrp'] = $cdr['Egress Trunk Group Name'];
                    $RECORD['egress_media'] = $cdr['Egress IP Circuit End Point'];

                    if ($RECORD['type'] == 'STOP') {
                        $RECORD['call_duration'] = $cdr['Call Service Duration'];

                        if (isset($cdr['Media Stream Stats']) && $cdr['Media Stream Stats']) {
                            $RECORD['ingress_lost_ptks'] = $cdr['Media Stream Stats'][7];
                            $RECORD['egress_lost_ptks'] = $cdr['Media Stream Stats'][13];
                        }
						if (isset($cdr['Ingress Protocol Variant Specific Data']) && $cdr['Ingress Protocol Variant Specific Data']) {
							$RECORD['ingress_callid'] = $cdr['Ingress Protocol Variant Specific Data'][1];
						}
						if (isset($cdr['Egress Protocol Variant Specific Data']) && $cdr['Egress Protocol Variant Specific Data']) {
							$RECORD['egress_callid'] = $cdr['Egress Protocol Variant Specific Data'][1];
						}
                    }

                    $RECORD['cdr_json'] = $cdr;

                    //print_r($RECORD);
                    ///if(\App\Sonus5kCDR::where('accounting_id', $RECORD['accounting_id'])->count()){
                    if (\App\Sonus5kCDR::where([['accounting_id', $RECORD['accounting_id']], ['gw_name', $RECORD['gw_name']]])->count()) {
                        //print "Found Record Matching Accounting ID:".$RECORD['accounting_id']." | ".$RECORD['start_time'].PHP_EOL;
                    } else {
                        echo 'Creating New Record: '.$RECORD['accounting_id'].PHP_EOL;
                        \App\Sonus5kCDR::firstOrCreate($RECORD);
                        //print_r($RECORD);
                    }
                }
            }
        }

        /*
        $INSERT['totalCalls'] = $totalCalls;
        $INSERT['stats'] = json_encode($STATS, true);
        print_r($INSERT);
        //return $STATS;

        $result = Sonus5kCDR::create($INSERT);

        print_r($result);

        */
    }

    public static function get_cdrs_from_file($SBC, $location)
    {
        $lasttwodays_calls = [];

        $sftp = new Net_SFTP($SBC, 2024);
        if (! $sftp->login(env('SONUSSFTPUSER'), env('SONUSSFTPPASS'))) {
            exit('Login Failed');
        }

        $currentfile = $sftp->get($location);

        $currentfile = explode(PHP_EOL, $currentfile);
        array_shift($currentfile);

        $today = Sonus5kCDR::get_today_in_sonus_format();
        $yesterday = Sonus5kCDR::get_yesterday_in_sonus_format();
		$daybefore = Sonus5kCDR::get_daybeforelast_in_sonus_format();
        foreach ($currentfile as $callrecord) {

            // Parse record to the the record type.
            $callrecord = explode(',', $callrecord);
            if ($callrecord[0] == 'STOP' || $callrecord[0] == 'ATTEMPT') {

                // Only Return entries that are in the last two days.
                //if ($callrecord[5] == $today || $callrecord[5] == $yesterday || $callrecord[5] == $daybefore) {
				if ($callrecord[5] == $today || $callrecord[5] == $yesterday) {
                    $callrecord = implode(',', $callrecord);
                    $lasttwodays_calls[] = $callrecord;
                }
            }

            // Pop off the first member of the array to reduce memory usage.
            array_shift($currentfile);
        }

        // Return the raw comma seperated Log entries not an array for the last 2 days.
        return implode(PHP_EOL, $lasttwodays_calls);
    }
}
