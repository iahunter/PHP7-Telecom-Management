<?php

namespace App\Console\Commands\DidScan;

use Illuminate\Console\Command;
use App\Did;

class Callmanager extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'DidScan:Callmanager';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan configured DIDs in Callmanager';

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
//		ini_set('soap.wsdl_cache_enable', "0");
//		ini_set('soap.wsdl_cache_ttl',0);
//		$wsdl = storage_path(env('CALLMANAGER_WSDL'));
//		print "wsdl file path is:".$wsdl.PHP_EOL;
		try {
			$cucm = new \CallmanagerAXL\Callmanager(env('CALLMANAGER_URL'),
													storage_path(env('CALLMANAGER_WSDL')),
													env('CALLMANAGER_USER'),
													env('CALLMANAGER_PASS')
													);
//			$sites = $cucm->get_site_names();
//			dd($sites);
			$didcrap = $cucm->get_route_plan_by_name('402938%');
			echo json_encode($didcrap).PHP_EOL;
		}catch (\Exception $e) {
			print "Callmanager blew uP: ".$e->getMessage().PHP_EOL;
			dd($e->getTrace());
		}
    }
}
