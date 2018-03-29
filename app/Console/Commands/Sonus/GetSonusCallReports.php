<?php

namespace App\Console\Commands\Sonus;

use DB;
use App\Calls;
use Carbon\Carbon;
use App\Sonus5kCDR;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class GetSonusCallReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sonus:save_call_reports_to_cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Saves the Call Graph Reports JSON to cache for web frontend fast loading! - Run in Cron every 5 mins';

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
        echo Carbon::now().' Starting: '.PHP_EOL;

        echo Carbon::now().' Starting: list_last_hour_top_attempt_counts_by_called_number_report '.PHP_EOL;
        $this->list_last_hour_top_attempt_counts_by_called_number_report();
        echo Carbon::now().' Starting: list_todays_top_attempt_counts_by_called_number_report '.PHP_EOL;
        $this->list_todays_top_attempt_counts_by_called_number_report();
        echo Carbon::now().' Starting: list_todays_top_attempt_counts_by_calling_number_report '.PHP_EOL;
        $this->list_todays_top_attempt_counts_by_calling_number_report();
        echo Carbon::now().' Starting: get_todays_attempt_report '.PHP_EOL;
        $this->get_todays_attempt_report();
        echo Carbon::now().' Starting: list_last_7days_callstats '.PHP_EOL;
        $this->list_last_7days_callstats();
        echo Carbon::now().' Starting: list_3_month_daily_call_peak_stats '.PHP_EOL;
        $this->list_3_month_daily_call_peak_stats();
        echo Carbon::now().' Starting: list_3_month_daily_call_peak_stats_sql '.PHP_EOL;
        $this->list_3_month_daily_call_peak_stats_sql();
        echo Carbon::now().' Starting: list_todays_pkt_loss_summary_report '.PHP_EOL;
        $this->list_todays_pkt_loss_summary_report();

        echo Carbon::now().' Complete: '.PHP_EOL;
    }

    protected function list_last_hour_top_attempt_counts_by_called_number_report()
    {
        $return = Sonus5kCDR::list_last_hour_top_attempt_counts_by_called_number_report();

        //print_r($return);

        // Name of Cache key.
        $key = 'Sonus5kCDR::list_last_hour_top_attempt_counts_by_called_number_report()';

        /* Call this from the controller to fetch the data in the cache*/
        /*
        if (Cache::has($key)) {
            //Log::info(__METHOD__.' Used Cache');
            return Cache::get($key);
        }
        */

        // Cache Calls for 10 Minutes - Put the $CALLS as value of cache.
        $time = Carbon::now()->addMinutes(10);
        Cache::put($key, $return, $time);
    }

    protected function list_todays_top_attempt_counts_by_called_number_report()
    {
        $return = Sonus5kCDR::list_todays_top_attempt_counts_by_called_number_report();

        //print_r($return);

        // Name of Cache key.
        $key = 'Sonus5kCDR::list_todays_top_attempt_counts_by_called_number_report()';

        /* Call this from the controller to fetch the data in the cache*/
        /*
        if (Cache::has($key)) {
            //Log::info(__METHOD__.' Used Cache');
            return Cache::get($key);
        }
        */

        // Cache Calls for 10 Minutes - Put the $CALLS as value of cache.
        $time = Carbon::now()->addMinutes(10);
        Cache::put($key, $return, $time);
    }

    protected function list_todays_top_attempt_counts_by_calling_number_report()
    {
        $return = Sonus5kCDR::list_todays_top_attempt_counts_by_calling_number_report();

        //print_r($return);

        // Name of Cache key.
        $key = 'Sonus5kCDR::list_todays_top_attempt_counts_by_calling_number_report()';

        /* Call this from the controller to fetch the data in the cache*/
        /*
        if (Cache::has($key)) {
            //Log::info(__METHOD__.' Used Cache');
            return Cache::get($key);
        }
        */

        // Cache Calls for 10 Minutes - Put the $CALLS as value of cache.
        $time = Carbon::now()->addMinutes(10);
        Cache::put($key, $return, $time);
    }

    protected function list_todays_pkt_loss_summary_report()
    {
        $return = Sonus5kCDR::list_todays_pkt_loss_summary_report();

        //print_r($return);

        // Name of Cache key.
        $key = 'Sonus5kCDR::list_todays_pkt_loss_summary_report()';

        /* Call this from the controller to fetch the data in the cache*/
        /*
        if (Cache::has($key)) {
            //Log::info(__METHOD__.' Used Cache');
            return Cache::get($key);
        }
        */

        // Cache Calls for 10 Minutes - Put the $CALLS as value of cache.
        $time = Carbon::now()->addMinutes(10);
        Cache::put($key, $return, $time);
    }

    protected function get_todays_attempt_report()
    {
        $return = Sonus5kCDR::list_todays_attempts_summary_report();

        //print_r($return);
        // Name of Cache key.
        $key = 'Sonus5kCDR::list_todays_attempts_summary_report()';

        /* Call this from the controller to fetch the data in the cache*/
        /*
        if (Cache::has($key)) {
            //Log::info(__METHOD__.' Used Cache');
            return Cache::get($key);
        }
        */

        // Cache Calls for 10 Minutes - Put the $CALLS as value of cache.
        $time = Carbon::now()->addMinutes(10);
        Cache::put($key, $return, $time);
    }

    protected function list_last_7days_callstats()
    {
        $currentDate = \Carbon\Carbon::now();
        $now = $currentDate->toDateTimeString();
        $weekago = $currentDate->subHours(168)->toDateTimeString();

        $calls = Calls::whereBetween('created_at', [$weekago, $now])->get();

        $stats = [];
        foreach ($calls as $call) {
            $call['stats'] = json_decode($call['stats']);
            $stats[] = $call;
        }

        // Name of Cache key.
        $key = 'calls:list_last_7days_callstats';

        // Cache Calls for 10 Minutes - Put the $CALLS as value of cache.
        $time = Carbon::now()->addMinutes(10);
        Cache::put($key, $stats, $time);
    }

    protected function list_3_month_daily_call_peak_stats_sql()
    {
        // This is a new version of returning the peak of each date so make our graph a little smoother.
        // Doing this entire function in one SQL query instead of using curser like above.

        $currentDate = \Carbon\Carbon::now();
        $end = $currentDate->toDateTimeString();
        $start = $currentDate->subMonth(3)->toDateTimeString();

        /* Trying to get this query to work.
        SELECT DATE(created_at) as created_at, MAX(totalCalls) as totalCalls
        FROM sbc_calls
        WHERE created_at > NOW() - INTERVAL 3 MONTH
        GROUP BY DATE(created_at)
        */

        // this could use some work to make better.
        $stats = DB::table('sbc_calls')
                ->select(DB::raw('DATE(created_at) as created_at'), DB::raw('MAX(totalCalls) as totalCalls'))
                ->groupBy(DB::raw('DATE(created_at)'))
                ->whereBetween('created_at', [$start, $end])->orderby('created_at')
                ->get();

        /*
        $response = [
                    'status_code'    => 200,
                    'success'        => true,
                    'message'        => '',
                    'result'         => $stats,
                    ];

        return response()->json($response);
        */

        // Name of Cache key.
        $key = 'calls:list_3_month_daily_call_peak_stats_sql';

        // Cache Calls for 10 Minutes - Put the $CALLS as value of cache.
        $time = Carbon::now()->addMinutes(10);
        Cache::put($key, $stats, $time);
    }

    protected function list_last_month_daily_call_peak_stats()
    {
        // Only used for 1 month. we are using the last 3 month graph instead.
        $currentDate = \Carbon\Carbon::now();
        $end = $currentDate->toDateTimeString();
        $start = $currentDate->subMonth(3)->toDateTimeString();

        $stats = [];
        /*
        $calls = Calls::whereBetween('created_at', [$start, $end])->get();

        foreach ($calls as $call) {
        */

        // Use cursor to conserve memory and iterate our database records in foreach loop.
        foreach (Calls::whereBetween('created_at', [$start, $end])->cursor() as $call) {
            $date = $call['created_at'];
            $date = Carbon::parse($call['created_at']);
            $date = $date->toDateString();

            $call['stats'] = json_decode($call['stats']);

            if (isset($stats[$date])) {
                if ($stats[$date]['totalCalls'] >= $call['totalCalls']) {
                    continue;
                }
            }

            $stats[$date] = $call;
        }

        // Name of Cache key.
        $key = 'calls:list_last_month_daily_call_peak_stats';

        // Cache Calls for 10 Minutes - Put the $CALLS as value of cache.
        $time = Carbon::now()->addMinutes(10);
        Cache::put($key, $stats, $time);
    }

    public function list_3_month_daily_call_peak_stats()
    {
        $currentDate = \Carbon\Carbon::now();
        $end = $currentDate->toDateTimeString();
        $start = $currentDate->subMonth(3)->toDateTimeString();

        $stats = [];

        // Use cursor to conserve memory and iterate our database records in foreach loop. slower than sql query because we need to iterate thur all records.
        foreach (Calls::whereBetween('created_at', [$start, $end])->cursor() as $call) {
            $date = $call['created_at'];
            $date = Carbon::parse($call['created_at']);
            $date = $date->toDateString();

            $call['stats'] = json_decode($call['stats']);

            if (isset($stats[$date])) {
                if ($stats[$date]['totalCalls'] >= $call['totalCalls']) {
                    continue;
                }
            }

            $stats[$date] = $call;
        }

        // Name of Cache key.

        $key = 'calls:list_3_month_daily_call_peak_stats';

        // Cache Calls for 10 Minutes - Put the $CALLS as value of cache.
        $time = Carbon::now()->addMinutes(10);
        Cache::put($key, $stats, $time);
    }
}
