<?php

namespace App\Console\Commands\CallManager;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class CucmPhoneNamesCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'callmanager:phone_names_cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        // Construct new cucm object
        $this->cucm = new \Iahunter\CallmanagerAXL\Callmanager(env('CALLMANAGER_URL'),
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
        try {
            $CUCM_PHONES = $this->cucm->get_phone_names();
        } catch (\Exception $E) {
            return $E->getMessage();
        }

        foreach ($CUCM_PHONES as $UUID => $PHONE) {
            $CUCM_PHONES[$UUID] = strtoupper($PHONE);
        }

        // Name of Cache key.
        $key = 'callmanager:phone_names_cache';

        // Cache Names for 5 seconds - Put the $CALLS as value of cache.
        print_r($CUCM_PHONES);
        $time = Carbon::now()->addSeconds(300);
        Cache::put($key, $CUCM_PHONES, $time);
    }
}
