<?php

namespace App\Console\Commands\CallManager;

use App\Didblock;
use App\Did;
use Illuminate\Console\Command;

class CucmNumberCleanup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'callmanager:cleanup_unused_numbers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleanup Numbers in Cucm that have no Devices assigned, No CallForwarding Acive, and No Mailbox in Cisco Unity';

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
        $didblocks = \App\Didblock::where([['country_code', '=', 1]])->get();

		$possible_deletes = [];
		foreach($didblocks as $didblock){
			
			 // Get the DID records matching $npanxx.'%' - Only Valid for NANP Numbers
			if (\App\Did::where([['parent', '=', $didblock->id]])->count()) {
				$dids = \App\Did::where([['parent', '=', $didblock->id]])->get();
				
				$dids = json_decode(json_encode($dids, true));
				
				foreach ($dids as $did) {
					if ($did->status == 'inuse') {
						// Check if this number has any assigned devices... Need to move this functionality to its own command and schedule.
						$did = (array) $did;
						//print_r($did);
						foreach ($did['assignments'] as $entry) {
							$entry = (array) $entry;
							if (isset($entry['routeDetail']) && ! $entry['routeDetail']) {
								//print "{$entry['dnOrPattern']} - This number needs looked at!!!".PHP_EOL;
								$possible_deletes[$didblock->id][$entry['uuid']] = $entry['dnOrPattern'];
							}
						}
					}
				}

				
			}
			
		}
		
		print_r($possible_deletes);
		
    }
}
