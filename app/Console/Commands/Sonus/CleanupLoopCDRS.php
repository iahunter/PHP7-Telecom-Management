<?php

namespace App\Console\Commands\Sonus;

use Carbon\Carbon;
use Illuminate\Console\Command;

class CleanupLoopCDRS extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sonus:cdr_cleanup_from_loop {number}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete CDRs generated from a Routing Loop {number}';

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
		if(env('SBC_MAINTENANCE')){
			echo "SBC Maintenance is going on. {$this->signature}... ".PHP_EOL; 
			return; 
		}
		
        $number = $this->argument('number');

        $this->cleanup_cdrs_from_today($number);

        /*
        $start = Carbon::parse('2018-03-27');
        $end = Carbon::parse('2018-03-28');
        // Change $start and $end if you want to search for records between two dates.
        $this->cleanup_cdrs_between_start_end($number, $start, $end);
        */
    }

    public function cleanup_cdrs_from_today($number)
    {
        $start = Carbon::now();
        $cutoffdate = $start->subHours(24);

        $now = Carbon::now()->toDateTimeString();
        echo $now." Starting Sonus CDR DB Cleanup for number {$number} after {$cutoffdate}...".PHP_EOL;

        $count = 0;
        $maxdeletecount = 1000;

        $count = \App\Sonus5kCDR::where('disconnect_time', '>', $cutoffdate)
                                        ->where('called_number', 'like', '%'.$number.'%')
                                        ->count();

        echo "Found {$count} records for prior to cutoff date: {$cutoffdate}...".PHP_EOL;

        // While count is greater than 0 keep running
        while ($count > 0) {
            $delete = \App\Sonus5kCDR::where('disconnect_time', '>', $cutoffdate)
                                        ->where('called_number', 'like', '%'.$number.'%')
                                        ->limit($maxdeletecount)
                                        ->delete();

            echo "Deleted {$delete} records...".PHP_EOL;

            $count = $count - $delete;
            echo $count.PHP_EOL;
        }

        echo 'Ding! Fries are done!'.PHP_EOL;
    }

    public function cleanup_cdrs_between_start_end($number, $start, $end)
    {
        $now = Carbon::now()->toDateTimeString();
        echo $now." Starting Sonus CDR DB Cleanup for number {$number} between {$start} and {$end}...".PHP_EOL;

        $count = 1;
        $maxdeletecount = 1000;

        $count = \App\Sonus5kCDR::whereBetween('disconnect_time', [$start, $end])
                                        ->where('called_number', 'like', '%'.$number.'%')
                                        ->count();

        echo "Found {$count} records between {$start} and {$end}...".PHP_EOL;

        // While count is greater than 0 keep running
        while ($count > 0) {
            $delete = \App\Sonus5kCDR::whereBetween('disconnect_time', [$start, $end])
                                        ->where('called_number', 'like', '%'.$number.'%')
                                        ->limit($maxdeletecount)
                                        ->delete();

            echo "Deleted {$delete} records...".PHP_EOL;

            $count = $count - $delete;
            echo $count.PHP_EOL;
        }

        echo 'Ding! Fries are done!'.PHP_EOL;
    }
}
