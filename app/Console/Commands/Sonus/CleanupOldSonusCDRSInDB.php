<?php

namespace App\Console\Commands\Sonus;

use Carbon\Carbon;
use Illuminate\Console\Command;

class CleanupOldSonusCDRSInDB extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sonus:cleanup_cdr_db';

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
        //
        $start = \Carbon\Carbon::now();

        echo "Starting Sonus CDR DB Cleanup at {$start}..".PHP_EOL;

        // Only store this many days of CDRs in the DB.
        $cutoffdate = $start->subDays(90);

        $count = 1;
        $maxdeletecount = 1000;

        while ($count > 0) {
            // While count is greater than 0 keep running
            $count = \App\Sonus5kCDR::where('start_time', '<', $cutoffdate)->count();
            echo "Found {$count} records prior to cutoff date: {$cutoffdate}...".PHP_EOL;

            //$cdrs = \App\Sonus5kCDR::where('start_time', '<', $cutoffdate)->limit($maxdeletecount)->get();

            //print_r($cdrs);

            $delete = \App\Sonus5kCDR::where('start_time', '<', $cutoffdate)->limit($maxdeletecount)->delete();

            echo "Deleted {$delete} records...".PHP_EOL;

            //$count = 0;
        }

        echo 'Ding! Fries are done!'.PHP_EOL;
    }
}
