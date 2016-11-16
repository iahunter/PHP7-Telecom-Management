<?php

namespace App\Console\Commands\CallManager;

use Illuminate\Console\Command;

class BuildCUCMSiteDefaults extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'callmanager:sitedefaults';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'DO NOT RUN!!! Custom Script - Builds All Site Default Dependencies for Globalized Dialplan and Normalization. Also blocked translations and such. ';

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
	
	public $results; 	// Array of results to return to user. 
	
	public $partitions = $this->cucm->get_object_type_by_site('%', 'RoutePartition')

	/**
     * Wrap CUCM Object adds with this wrapper. 
     *
     * @return mixed
     */
	public function wrap_add_object($DATA, $TYPE)
    {
        try {
            $REPLY = $this->cucm->add_object_type_by_assoc($DATA, $TYPE);
            print "{$TYPE} CREATED: {$REPLY}\n\n";
        } catch (\Exception $E) {
            $EXCEPTION = "Exception adding object type {$DATA['name']}:".
                  "{$E->getMessage()}".
                  "Stack trace:\n".
                  "{$E->getTraceAsString()}".
                  "Data sent:\n";
            $DATA['exception'] = $EXCEPTION;
            $this->results[$TYPE] = $DATA;
        }
    }
	
	
	
	/**
     * Execute the console command.
     *
     * @return mixed
     */
	 
    public function handle()
    {
        $DEV = true;                                        // Toggle Development
        $DEBUG = true;
        $COUNT = 0;
		
		// 1. Add a 911 route partition

        // Calculated variables
        $TYPE = 'RoutePartition';
        // Prepared datastructure
        $DATA = [
                'name'                            => 'PT_'.$SITE.'_911',
                'description'                     => $SITE.' 911 Calling',
                'useOriginatingDeviceTimeZone'    => 'true',
                ];

        // Check if the object already exists. If it isn't then add it.
        if (! empty($this->partitions)) {
            if (in_array($DATA['name'], $this->partitions)) {
                $this->results[$TYPE] = "Skipping... {$DATA['name']} already exists.";
            } else {
                $this->wrap_add_object($DATA, $TYPE, $SITE);
            }
        } else {
            $this->wrap_add_object($DATA, $TYPE, $SITE);
        }
	}
        
}
