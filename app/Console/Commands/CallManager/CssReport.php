<?php

namespace App\Console\Commands\CallManager;

use Illuminate\Console\Command;

class CssReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'callmanager:css-report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pull Css Details Report from CUCM';

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
    }
}
