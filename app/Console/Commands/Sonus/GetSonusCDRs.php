<?php

namespace App\Console\Commands\Sonus;

use App\Sonus5kCDR;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
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
    protected $description = 'Fetch new CDRs from Sonus SBC and write to Database';

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
     * add a comment here.
     * @return mixed
     */
    public function handle()
    {
        if (env('SBC_MAINTENANCE')) {
            echo "SBC Maintenance is going on. {$this->signature}... ".PHP_EOL;

            return;
        }

        $starttime = Carbon::now();

        $this->SBCS = array_filter($this->SBCS);

        if (! count($this->SBCS)) {
            echo 'No SBCs Configured. Killing job.'.PHP_EOL;

            return;
        }

        // SBC SFTP Creds
        $username = env('SONUSSFTPUSER');
        $password = env('SONUSSFTPPASS');

        $cutoff_hours = 48; 				// Number of hours to insert into the DB. Anything past that ignore it.

        $now = Carbon::now();
        echo 'Starting - '.$now.PHP_EOL;

        foreach ($this->SBCS as $SBC) {
            if (env('SONUS_DOMAIN_NAME')) {
                $hostname = $SBC.'.'.env('SONUS_DOMAIN_NAME');
            } else {
                $hostname = $SBC;
            }

            // Get latest CDR File.
            echo '##############################################'.PHP_EOL;
            echo "Starting - {$SBC} - ".$now.PHP_EOL;
            echo "Fetching CDR Files from {$SBC}".PHP_EOL;

            $locations = \App\Sonus5kCDR::get_cdr_log_names($hostname, $username, $password);

            foreach ($locations as $key => $location) {
                $timestamp = Carbon::createFromTimestamp($key)->toDateTimeString();
                //print "File Timestamp: ".$timestamp.PHP_EOL;

                $cutoff = $now->copy()->subHours($cutoff_hours);
                //print "Cutoff Timestamp: ".$cutoff.PHP_EOL;

                if ($cutoff >= $timestamp) {
                    //print $timestamp." Is less than 48 hrs ago. Skipping... ".PHP_EOL;
                    continue;
                } else {
                    echo PHP_EOL;
                    echo 'Filename: '.$location.PHP_EOL;
                    echo 'Opening File to inspect contents...'.PHP_EOL;
                }

                // Login to Sonus and get File via SFTP
                $file = Sonus5kCDR::get_file_from_location($hostname, $username, $password, $location);

                $cdr_array = Sonus5kCDR::parse_cdr_file_into_array($file); 								// Parse CSV into workable Array
                $cdr_array = Sonus5kCDR::get_completed_cdrs_from_array($cdr_array); 					// Get Stop and Attempts records only from array

                $record_count = count($cdr_array);
                echo 'Found '.$record_count.' completed records.. Filter out records older than cutoff of: '.$cutoff_hours.' hours. '.PHP_EOL;

                /*
                foreach($cdr_array as $cdr){
                    //print_r($cdr);
                    print "{$cdr['Record Type']} {$cdr['Gateway Name']} {$cdr['Accounting ID']} {$cdr['Start Time (MM/DD/YYYY)']} {$cdr['Start Time (HH/MM/SS.s)']}" . PHP_EOL;

                }
                */

                $cdr_array = Sonus5kCDR::filter_out_cdrs_older_than_cutoff($cdr_array, $cutoff_hours); 	// Filter out records older than cutoff date.

                $record_count = count($cdr_array);
                echo 'Found '.$record_count.' completed records after the cutoff date to check db for insertion'.PHP_EOL;

                $cdr_array = Sonus5kCDR::check_db_for_records_return_records_to_insert($cdr_array); // Find starting place if some records already exist in the array... divide and conquer!!!!!

                if ($cdr_array) {
                    echo 'Insert '.(count($cdr_array)).' cdr records into the DB... '.PHP_EOL;
                } else {
                    echo 'Nothing to insert... Moving on...'.PHP_EOL;
                    continue;
                }

                $count_total = count($cdr_array);
                $i = 0;

                // We could use array_chunk here if we needed to for speed... However I'm not sure if this could lead to duplicates. Also not sure if I could do that with Kafka. Would also need to move formating above. Avoiding for now...
                /* Example:
                    foreach (array_chunk($cdr_array, 1000) as $chunk) {
                        Sonus5kCDR::insert($chunk);            // insert the array into the database. Much faster than inserting individual rows.
                    }
                */
                foreach ($cdr_array as $cdr) {
                    $i++; 																// Count for display purposes.
                    $RECORD = Sonus5kCDR::get_db_format_from_cdr($cdr);
                    $DST_CHECK = new \Carbon\Carbon($RECORD['start_time']);

                    if (Sonus5kCDR::check_db_for_record($RECORD)) {																								// Check if record exists in the db.
                        echo "Found Record {$i} of {$count_total}: Accounting ID:".$RECORD['accounting_id'].' | '.$RECORD['start_time'].' | '.$RECORD['disconnect_time'].PHP_EOL;
                        continue;
                    }

                    // ********
                    // Adding Check for Daylight Savings time calls in UTC. This is a temp workaround for an issue with MySQL doing insert from 2am to 3am because our Server is set to CST. There is no such thing as 2am CT during DST spring ahead. It skips form 1:59am to 3am.
                    // Was getting error: SQLSTATE[22007]: Invalid datetime format: 1292 Incorrect datetime value: '2020-03-08 02:01:17.4' for column 'start_time' at row 1
                    $TIMECHANGE_START = '2020-03-08 02:00:00.000000';
                    $TIMECHANGE_END = '2020-03-08 03:00:00.000000';

                    if ($RECORD['start_time'] > $TIMECHANGE_START && $RECORD['start_time'] < $TIMECHANGE_END) {																								// Check if record exists in the db.
                        echo "TIME CHANGE ISSUE WITH INSERT: Skippping Record {$i} of {$count_total}: Accounting ID:".$RECORD['accounting_id'].' | '.$RECORD['start_time'].' | '.$RECORD['disconnect_time'].PHP_EOL;
                        continue;
                    }
                    // End of DST Check. Need to fix this on new server by putting the server into UTC time.
                    // ********

                    else {
                        echo "Creating New Record: {$i} of {$count_total}: ".$RECORD['accounting_id'].' | '.$RECORD['start_time'].' | '.$RECORD['disconnect_time'].PHP_EOL;

                        \App\Sonus5kCDR::firstOrCreate($RECORD);			// Try to create the new record in the db.

                        // Ship cdr record to Kafka for Elastic Search capabilities.
                        if (getenv('KAFKA_BROKERS')) {

                            //$RECORD['disconnect_time'] = Carbon::parse($RECORD['disconnect_time']);

                            // instantiate a Kafka producer config and set the broker IP
                            $config = \Kafka\ProducerConfig::getInstance();
                            $config->setMetadataBrokerList(getenv('KAFKA_BROKERS'));
                            // instantiate new Kafka producer
                            $producer = new \Kafka\Producer();

                            // ship data to Kafka
                            try {
                                // Try to send to Kafka
                                $result = $producer->send([
                                    [
                                        'topic' => 'sonus_cdr',
                                        'value' => json_encode($RECORD),
                                    ],
                                ]);

                                // check for and log errors
                                if ($result[0]['data'][0]['partitions'][0]['errorCode']) {
                                    print_r($result);
                                    Log::error('[!] [KAFKA_WARNING] Error sending CDR alert to Kafka: '.$result[0]['data'][0]['partitions'][0]['errorCode']);
                                } else {
                                    //Log::info('[+] CAS high alert successfully sent to Kafka: '.$alert['alert_id']);
                                }
                            } catch (\Exception $E) {
                                //echo "{$E->getMessage()}".PHP_EOL;
                            }
                        }
                    }
                }
            }
        }

        echo 'Start Time: '.$starttime.PHP_EOL;
        echo 'Stop Time: '.Carbon::now().PHP_EOL;
    }
}
