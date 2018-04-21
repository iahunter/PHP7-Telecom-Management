<?php

namespace App\Console\Commands\Sonus;

use App\Calls;
use App\Sonus5k;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class SonusActiveCallsCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sonus:listactivecalls-to-cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cache Current Call Summary for UI Fast Loading - Runs this every 5 seconds by store-sonus-active-calls-to-cache.sh - Run in Cron every minute';

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
        //Log::info(__METHOD__.' Did not Use Cache');
        $CALLS = [];
        foreach ($this->SBCS as $SBC) {
			$CALLS[$SBC] = Sonus5k::listactivecalls($SBC);
        }

        // Name of Cache key.
        $key = 'listactivecalls';

        // Cache Calls for 5 seconds - Put the $CALLS as value of cache.
		print_r($CALLS);
        $time = Carbon::now()->addSeconds(10);
        Cache::put($key, $CALLS, $time);
    }
}
