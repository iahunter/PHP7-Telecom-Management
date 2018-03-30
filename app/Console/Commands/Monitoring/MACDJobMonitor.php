<?php

namespace App\Console\Commands\Monitoring;

use Carbon\Carbon;
use Illuminate\Console\Command;
use DB;



class MACDJobMonitor extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'monitoring:macd_job_monitor';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Montitor for Stuck MACD Work and restart supervisor';

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
		$jobs = $this->check_for_stuck_jobs();
		//$jobs = json_decode($jobs, true); 			// Convert Collection to an array
		print_r($jobs); 
		
		sleep(600); // Wait for 10 mins and then check the queue again to see if any of these are still stuck in there. 
		
		$newjobs = $this->check_for_stuck_jobs();
		
		print_r($newjobs); 
		
		$sup_restart = false;
		
		foreach($jobs as $job){
			if(in_array($job, $newjobs)){
				//print "Found Job still stuck in queue... ".PHP_EOL;	
				$sup_restart = true;		// Set if if found an old job inside the newjobs array in case its stuck in queue. 					
			}
		}
		
		print "Supervisor Restart: ".$sup_restart.PHP_EOL . PHP_EOL;
		
		// If we set supervisor restart above, Restart the supervisor service. 
		if ($sup_restart){
			print "Restarting Supervisor...".PHP_EOL;
			shell_exec('service supervisor restart'); 
			print "Supervisor Restart Complete.".PHP_EOL;
		}
		
    }
	
	public function check_for_stuck_jobs()
    {
        $jobs = DB::table('jobs')
                ->select('id' ,DB::raw('created_at as created_at'), DB::raw('attempts'))
                ->get();
		
		$jobs = json_decode($jobs, true); // Convert Collection to an array
		$jobs_array = []; 
		
		foreach($jobs as $key => $job){
			$jobs_array[$job['id']] = $job; 
		}
		return $jobs_array; 			
    }
}
