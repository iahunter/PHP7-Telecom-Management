<?php

namespace App\Console\Commands\Sonus;

use App\Calls;
use App\Sonus5k;
use Illuminate\Console\Command;

class SonusCalls extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sonus:write-callsummary-db';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Submit Current Call Summary to Database';

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
        $totalCalls = 0;
        $STATS = [];
        foreach ($this->SBCS as $SBC) {
            $STAT = Sonus5k::getactivecallstats($SBC);
            //print_r($STAT);
            //$STAT = $STAT['sonusActiveCall:callCountStatus']; 		// Removed 042118 when changing to xml
            $sbccalls = $STAT['totalCalls'];
            $totalCalls = $totalCalls + $sbccalls;
            $STATS[$SBC] = $STAT;
        }
        $INSERT['totalCalls'] = $totalCalls;
        $INSERT['stats'] = json_encode($STATS, true);
        print_r($INSERT);
        //return $STATS;

        $result = Calls::create($INSERT);

        print_r($result);
    }
}
