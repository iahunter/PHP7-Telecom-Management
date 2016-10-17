<?php

namespace App\Console\Commands\CallManager;

use Illuminate\Console\Command;

class SiteDetailsReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'callmanager:site-details-report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pull Site Details Report from CUCM';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
		// Construct new cucm object
		$this->cucm = new \CallmanagerAXL\Callmanager(env('CALLMANAGER_URL'),
                                                    storage_path(env('CALLMANAGER_WSDL')),
                                                    env('CALLMANAGER_USER'),
                                                    env('CALLMANAGER_PASS')
                                                    );
		
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
		echo "Under Construction".PHP_EOL;
		
		try {
            $sites = $this->cucm->get_site_names();
            //$sites = ["KHONEOMA"];

            if (! count($sites)) {
                throw new \Exception('Indexed results from call mangler is empty');
            }
			
            print_r($sites);
        } catch (\Exception $e) {
            echo 'Callmanager blew uP: '.$e->getMessage().PHP_EOL;
            dd($e->getTrace());
		}
    }
}
