<?php

namespace App\Console\Commands\Sonus;

use DB;
use App\Sonus5kCDR;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use App\Calls;

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
    protected $description = 'Saves the Call Report JSON to cache for user requested line graph - improves performance';

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
        
		$this->get_todays_attempt_report();
		$this->list_last_7days_callstats();
		$this->list_3_month_daily_call_peak_stats();
		$this->list_3_month_daily_call_peak_stats_sql();
		
    }
	
	protected function get_todays_attempt_report()
    {

		$return = [];
		
        $hours = 24;

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

            //DB::enableQueryLog();

            $calls = \App\Sonus5kCDR::groupBy('disconnect_reason')
                ->select('disconnect_reason', DB::raw('count(*) as total'))
                ->whereBetween('start_time', [$start, $end])
                ->where('type', 'ATTEMPT')
                ->get();

            //return DB::getQueryLog();

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


		// Name of Cache key.
        $key = 'sonus:get_todays_attempt_report';

		/* Call this from the controller to fetch the data. 

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
