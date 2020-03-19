<?php

namespace App\Console\Commands\CallManager;

use App\Cucmphoneconfigs;
use App\CucmPhoneStats;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GetCucmPhoneStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cisco_phone:get_phone_stats';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run Report that compiles phone counts and registration summary info and it inserts into the cucmphonestats table';

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
        $start = Carbon::now();
        echo 'Starting - cisco_phone:get_phone_stats - '.$start.PHP_EOL;

        // Get Phone Totals
        $insert = [];
        $insert['type'] = 'phonereport';
        $insert['total'] = Cucmphoneconfigs::get_active_phone_count();
        $insert['registered'] = Cucmphoneconfigs::get_phone_registered_count();
        $insert['stats'] = Cucmphoneconfigs::get_phone_registered_count_by_type();

        // Insert new Record
        $record = CucmPhoneStats::create($insert);

        $end = Carbon::now();

        echo 'Completed - cisco_phone:get_phone_stats - '.$end.PHP_EOL;
    }
}
