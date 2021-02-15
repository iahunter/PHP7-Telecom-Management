<?php

/*
This command is using an SFTP Server as a middle man to upload and download CDR files from CUCM.

CUCM is configured to upload CDR Records to the SFTP Server under Cisco Unified Serviceability.
Tools > CDR Management
    Add a New Billing server and add the IP or Hostname of your SFTP Server, username, password, and directory to store and fetch the CDR and CMR Files.

Enter the SFTP Server settings in the .env file of the TMS Application Root Directory.
    CUCMCDR_SERVER=1.1.1.1
    CUCMCDR_USER=admin
    CUCMCDR_PASS=password
    CUCMCDR_DIR=/home/USER/CDR/

This Command Deletes the file off the server after it extracts the Call Records and inserts it into the Database as a cleanup.

If that result is not desired, comment out $sftp->deletes from this file

*/

namespace App\Console\Commands\CallManager;

use App\CucmCDR;
use App\CucmCMR;
use Carbon\Carbon;
use Illuminate\Console\Command;
use phpseclib\Net\SFTP as Net_SFTP;

class GetCucmCDRs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'callmanager:getcdrs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get CDR Records from CUCM and insert into the DB';

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
        $logs = CucmCDR::get_log_names(); // Get Logs from SFTP Server
        // Add logic to check if keys exist for cmr and cdr.
        if ($logs) {
            if (array_key_exists('cdrs', $logs)) {
                //print Carbon::now()." Found: ".count($logs['cdrs'])." CDR Logs".PHP_EOL;
                $this->get_cdrs_from_file($logs);	// Parse and insert each CDR record into DB.
            }
            if (array_key_exists('cmrs', $logs)) {
                //print Carbon::now()." Found: ".count($logs['cmrs'])." CMR Logs".PHP_EOL;
                $this->get_cmrs_from_file($logs);	// Parse and insert each CMR record into DB.
            }
        }
    }

    public static function get_cdrs_from_file($logs)
    {
        $sftp = new Net_SFTP(env('CUCMCDR_SERVER'));
        if (! $sftp->login(env('CUCMCDR_USER'), env('CUCMCDR_PASS'))) {
            exit('Login Failed');
        }

        //print_r($logs);

        foreach ($logs['cdrs'] as $location) {
            $currentfile = $sftp->get($location);

            $currentfile = explode(PHP_EOL, $currentfile);

            $headers = array_shift($currentfile);  // Trim off the Headers
            $headers = explode(',', $headers); // Convert to Array

            //print_r($headers);
            array_shift($currentfile); // Trim off the Types
            array_pop($currentfile); // Trim off the last line which is blank.

            $count = count($currentfile);
            //echo "Found {$count} Records... ".PHP_EOL;
            //print_r($currentfile);

            foreach ($currentfile as $callrecord) {
                $raw = $callrecord;

                // Parse record to the the record type.
                $callrecord = explode(',', $callrecord);

                //print_r($callrecord);

                if (count($callrecord) > 1) {
                    try {
                        $callrecord_array = [];
                        $callrecord_array = CucmCDR::cdr_key_map_to_headers($headers, $callrecord);

                        $INSERT = [];

                        $INSERT['globalCallID_callId'] = $callrecord_array['globalCallID_callId'];
                        $INSERT['origLegCallIdentifier'] = $callrecord_array['origLegCallIdentifier'];

                        //$INSERT['dateTimeConnect'] = $callrecord_array['dateTimeConnect'];
                        $INSERT['dateTimeConnect'] = CucmCDR::timedateformat($callrecord_array['dateTimeConnect']); 	// Carbon format datetime

                        //$INSERT['dateTimeDisconnect'] = $callrecord_array['dateTimeDisconnect'];
                        $INSERT['dateTimeDisconnect'] = CucmCDR::timedateformat($callrecord_array['dateTimeDisconnect']); 	// Carbon format datetime

                        $INSERT['duration'] = $callrecord_array['duration'];
                        $INSERT['callingPartyNumber'] = $callrecord_array['callingPartyNumber'];

                        $INSERT['originalCalledPartyNumber'] = $callrecord_array['originalCalledPartyNumber'];
                        $INSERT['finalCalledPartyNumber'] = $callrecord_array['finalCalledPartyNumber'];

                        $INSERT['origDeviceName'] = $callrecord_array['origDeviceName'];
                        $INSERT['destDeviceName'] = $callrecord_array['destDeviceName'];
                        $INSERT['origIpv4v6Addr'] = $callrecord_array['origIpv4v6Addr'];
                        $INSERT['destIpv4v6Addr'] = $callrecord_array['destIpv4v6Addr'];

                        $INSERT['originalCalledPartyPattern'] = $callrecord_array['originalCalledPartyPattern'];
                        $INSERT['finalCalledPartyPattern'] = $callrecord_array['finalCalledPartyPattern'];
                        $INSERT['lastRedirectingPartyPattern'] = $callrecord_array['lastRedirectingPartyPattern'];

                        $INSERT['cdrraw'] = $raw;

                        //print_r($INSERT);

                        $result = CucmCDR::create($INSERT);

                        //print_r($callrecord_array);
                    } catch (\Exception $e) {
                        echo Carbon::now().' Error: ';
                        echo $e->getMessage().PHP_EOL;
                    }
                } else {
                    //echo 'Count not greater than 1... Discarding record...'.PHP_EOL;
                }
            }

            // Delete the file when we are done with it.
            //echo 'Attempting to Delete: '.$location.PHP_EOL;
            $deleted = $sftp->delete($location, false);
            if ($deleted) {
                //echo 'Deleted: '.$location.PHP_EOL;
            } else {
                echo 'Failed to Delete: '.$location.PHP_EOL;
            }

            //die();
        }
    }

    public static function get_cmrs_from_file($logs)
    {
        $sftp = new Net_SFTP(env('CUCMCDR_SERVER'));
        if (! $sftp->login(env('CUCMCDR_USER'), env('CUCMCDR_PASS'))) {
            exit('Login Failed');
        }

        //print_r($logs);

        foreach ($logs['cmrs'] as $location) {
            $currentfile = $sftp->get($location);

            $currentfile = explode(PHP_EOL, $currentfile);

            $headers = array_shift($currentfile);  // Trim off the Headers
            $headers = explode(',', $headers); // Convert to Array

            //print_r($headers);
            array_shift($currentfile); // Trim off the Types
            array_pop($currentfile); // Trim off the last line which is blank.

            $count = count($currentfile);
            //echo "Found {$count} Records... ".PHP_EOL;
            //print_r($currentfile);

            foreach ($currentfile as $callrecord) {
                $raw = $callrecord;

                // Parse record to the the record type.
                $callrecord = explode(',', $callrecord);

                //print_r($callrecord);

                if (count($callrecord) > 1) {
                    try {
                        $callrecord_array = [];
                        $callrecord_array = CucmCMR::cmr_key_map_to_headers($headers, $callrecord);

                        //print_r($callrecord_array);
                        //die();

                        $INSERT = [];

                        $INSERT['globalCallID_callId'] = $callrecord_array['globalCallID_callId'];

                        //$INSERT['dateTimeConnect'] = $callrecord_array['dateTimeConnect'];
                        $INSERT['dateTimeStamp'] = CucmCMR::timedateformat($callrecord_array['dateTimeStamp']); 	// Carbon format datetime

                        $INSERT['directoryNum'] = $callrecord_array['directoryNum'];
                        $INSERT['callIdentifier'] = $callrecord_array['callIdentifier'];

                        $INSERT['directoryNumPartition'] = $callrecord_array['directoryNumPartition'];
                        $INSERT['deviceName'] = $callrecord_array['deviceName'];

                        $INSERT['varVQMetrics'] = $callrecord_array['varVQMetrics'];

                        $INSERT['numberPacketsSent'] = $callrecord_array['numberPacketsSent'];
                        $INSERT['numberPacketsReceived'] = $callrecord_array['numberPacketsReceived'];
                        $INSERT['jitter'] = $callrecord_array['jitter'];
                        $INSERT['numberPacketsLost'] = $callrecord_array['numberPacketsLost'];

                        // Calculate Percentage of Loss
                        if ($callrecord_array['numberPacketsReceived']) {
                            $INSERT['packetLossPercent'] = ($callrecord_array['numberPacketsLost'] / ($callrecord_array['numberPacketsLost'] + $callrecord_array['numberPacketsReceived'])) * 100;
                        } else {
                            $INSERT['packetLossPercent'] = 0;
                            //print "{$callrecord_array['globalCallID_callId']}: PacketLoss = 0".PHP_EOL;
                        }

                        //print $INSERT['packetLossPercent'].PHP_EOL;

                        $INSERT['cmrraw'] = $raw;

                        //print_r($INSERT);

                        $result = CucmCMR::create($INSERT);

                        //print_r($callrecord_array);
                    } catch (\Exception $e) {
                        echo Carbon::now().' Error: ';
                        echo $e->getMessage().PHP_EOL;
                    }
                } else {
                    //echo 'Count not greater than 1... Discarding record...'.PHP_EOL;
                }
            }

            // Delete the file when we are done with it.
            //echo 'Attempting to Delete: '.$location.PHP_EOL;
            $deleted = $sftp->delete($location, false);
            if ($deleted) {
                //echo 'Deleted: '.$location.PHP_EOL;
            } else {
                echo 'Failed to Delete: '.$location.PHP_EOL;
            }
        }
    }
}
