<?php

namespace App\Console\Commands\CallManager;

use Carbon\Carbon;
use Illuminate\Console\Command;

class CleanupOldCucmCDRSInDB extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'callmanager:cleanup_cdr_db';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deletes CDR Records older than 90 days';

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
		$start = \Carbon\Carbon::now();
		$cutoffdate = $start->subDays(90);
		
        $this->cleanup_cdr_table($cutoffdate); 
        $this->cleanup_cmr_table($cutoffdate); 
		
		$end = \Carbon\Carbon::now();
		
        echo "{$end} Ding! Fries are done!".PHP_EOL;
    }
	
	public function cleanup_cdr_table($cutoffdate)
    {
        //
        $start = \Carbon\Carbon::now();

        echo "Starting CUCM CDR DB Cleanup at {$start}..".PHP_EOL;

        $count = 1;
        $maxdeletecount = 1000;

        while ($count > 0) {
            // While count is greater than 0 keep running
            $count = \App\CucmCDR::where('dateTimeDisconnect', '<', $cutoffdate)->count();
            echo "Found {$count} records prior to cutoff date: {$cutoffdate}...".PHP_EOL;

            //$cdrs = \App\CucmCDR::where('dateTimeDisconnect', '<', $cutoffdate)->limit($maxdeletecount)->get();

            //print_r($cdrs);

            $delete = \App\CucmCDR::where('dateTimeDisconnect', '<', $cutoffdate)->limit($maxdeletecount)->delete();

            echo "Deleted {$delete} records...".PHP_EOL;

            //$count = 0;
        }

        echo 'Ding! Fries are done!'.PHP_EOL;
    }
	
	public function cleanup_cmr_table($cutoffdate)
    {
        //
        $start = \Carbon\Carbon::now();

        echo "Starting CUCM CMR DB Cleanup at {$start}..".PHP_EOL;

        $count = 1;
        $maxdeletecount = 1000;

        while ($count > 0) {
            // While count is greater than 0 keep running
            $count = \App\CucmCMR::where('dateTimeStamp', '<', $cutoffdate)->count();
            echo "Found {$count} records prior to cutoff date: {$cutoffdate}...".PHP_EOL;

            //$cdrs = \App\CucmCMR::where('dateTimeStamp', '<', $cutoffdate)->limit($maxdeletecount)->get();

            //print_r($cdrs);

            $delete = \App\CucmCMR::where('dateTimeStamp', '<', $cutoffdate)->limit($maxdeletecount)->delete();

            echo "Deleted {$delete} records...".PHP_EOL;

            //$count = 0;
        }

        echo 'Ding! Fries are done!'.PHP_EOL;
    }
}
