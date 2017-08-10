<?php

namespace App\Console\Commands\CallManager;

use App\Did;
use App\Cupi;
use App\Didblock;
use Carbon\Carbon;
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
        // Construct new cucm object
        $this->cucm = new \CallmanagerAXL\Callmanager(env('CALLMANAGER_URL'),
                                                    storage_path(env('CALLMANAGER_WSDL')),
                                                    env('CALLMANAGER_USER'),
                                                    env('CALLMANAGER_PASS')
                                                    );

        $this->svn = env('CUCM_SVN');

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $start = Carbon::now();

        echo $start;

        $didblocks = \App\Didblock::where([['country_code', '=', 1]])->get();

        $count = 0;
        $possible_deletes = [];
        foreach ($didblocks as $didblock) {
            $sitecode = $didblock->name;

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
							
							// Only look at Lines with Type Device. This will ignore transpatterns and meet me numbers from report. 
							if($entry['type'] == "Device"){
								if (isset($entry['routeDetail']) && ! $entry['routeDetail']) {
									//print "{$entry['dnOrPattern']} - This number needs looked at!!!".PHP_EOL;
									$possible_deletes[$didblock->id][$entry['uuid']] = $entry['dnOrPattern'];
									$count++;
								}
							}
                            
                        }
                    }
                }
            }
        }

        print_r($possible_deletes);
        echo "Found {$count} numbers".PHP_EOL;

        $TYPE = 'Line';
        $lines_to_delete = [];
        $lines_to_delete_count = 0;
        $lines_with_cfa_active = [];
        $lines_with_cfa_active_count = 0;
        $lines_with_mailbox_built = [];
        $lines_with_mailbox_built_count = 0;
        $lines_with_other_usages = [];
        $lines_with_other_usages_count = 0;
        foreach ($possible_deletes as $blockid => $line) {
            $didblock = \App\Didblock::find($blockid);
            $sitecode = $didblock->name;

            echo "Sitecode: {$sitecode}".PHP_EOL;

            foreach ($line as $uuid => $number) {
                //$uuid = $uuid[0];
                try {
                    $linedetails = $this->cucm->get_object_type_by_uuid($uuid, $TYPE);

                    $mailbox_details = Cupi::findmailboxbyextension($linedetails['pattern']);

                    if ($mailbox_details['response']['@total'] > 0) {
                        $mailbox = $mailbox_details['response']['User'];
                        $mailbox = ['Alias'            => $mailbox['Alias'],
                                    'DisplayName'      => $mailbox['DisplayName'],
                                    'FirstName'        => $mailbox['FirstName'],
                                    'LastName'         => $mailbox['LastName'],
                                    'DtmfAccessId'     => $mailbox['DtmfAccessId'],
                                    'AD User Found'    => '',

                                    ];

                        if (isset($mailbox_details['response']['user']['Alias']) && $mailbox_details['response']['user']['Alias']) {
                            $username = $mailbox_details['response']['user']['Alias'];
                            $username = $this->Auth->getUserLdapPhone($username);

                            //print_r($username.user);
                            //die();
                            if ($username) {
                                $mailbox['AD User Found'] = $username.user;
                            }
                        }

                        //print_r($mailbox);
                    } else {
                        $mailbox = false;
                    }

                    // Check if CFA is set.

                    $line_summary = [
                                        'uuid'                      => $linedetails['uuid'],
                                        'pattern'                   => $linedetails['pattern'],
                                        'callForwardAll'            => $linedetails['callForwardAll']['destination'],
                                        'description'               => $linedetails['description'],
                                        'associatedDevices'         => $linedetails['associatedDevices'],
                                        'mailbox'                   => $mailbox,
                                        'sitecode'                  => $sitecode,
                                        'usage'                     => $linedetails['usage'],
                                        ];

                    // Only look at lines that are assigned a usage value of Device.
                    if ($linedetails['usage'] == 'Device') {
                        if ($linedetails['callForwardAll']['destination'] == '') {
                            if (! $mailbox) {
                                $lines_to_delete[$linedetails['uuid']] = $line_summary;
                                $lines_to_delete_count++;
                                print_r($lines_to_delete[$linedetails['uuid']]);
                                echo "{$linedetails['pattern']} is Ready to Delete...".PHP_EOL;

                                // Call Delete Function here...
                            }
                            if ($mailbox) {
                                $lines_with_mailbox_built[$linedetails['uuid']] = $line_summary;
                                $lines_with_mailbox_built_count++;
                                print_r($lines_with_mailbox_built[$linedetails['uuid']]);
                                echo "{$linedetails['pattern']} is has a mailbox built and cannot delete...".PHP_EOL;
                            }
                        } elseif ($linedetails['callForwardAll']['destination'] != '') {
                            $lines_with_cfa_active[$linedetails['uuid']] = $line_summary;
                            $lines_with_cfa_active_count++;
                            echo "{$linedetails['pattern']} is Forwarded to: {$linedetails['callForwardAll']['destination']}...".PHP_EOL;
                        } else {
                            echo "Something jacked up... {$uuid} {$number}".PHP_EOL;
                        }
                    } else {
                        $lines_with_other_usages[$linedetails['uuid']] = $line_summary;
                        $lines_with_other_usages_count++;
                    }
                } catch (\Exception $e) {
                    echo $e->getMessage();
                    echo 'Call Mnaager blew up'.PHP_EOL;
                    continue;
                }

                //print_r($linedetails);
            }
        }

        $results = [];
        $results['Lines to Delete'] = $lines_to_delete;
        $results['Lines with a mailbox'] = $lines_with_mailbox_built;
        $results['Lines with Call Forward Active'] = $lines_with_cfa_active;
		$results['Lines Found with other Usages'] = $lines_with_other_usages;



        echo '###########################################################################'.PHP_EOL;

        echo "lines_to_delete_count: {$lines_to_delete_count}".PHP_EOL;
        echo "lines_with_cfa_active_count: {$lines_with_cfa_active_count}".PHP_EOL;
        echo "lines_with_mailbox_built_count: {$lines_with_mailbox_built_count}".PHP_EOL;
		echo "lines_with_other_usages_count: {$lines_with_other_usages_count}".PHP_EOL;

        $end = Carbon::now();
        echo PHP_EOL;
        echo 'Start Time: '.$start.PHP_EOL;
        echo 'End Time: '.$end.PHP_EOL;

        echo '###########################################################################'.PHP_EOL;

         // Save Site Config as JSON and upload to subversion for change tracking.
        $svn_save = json_encode($results, JSON_PRETTY_PRINT);

        echo 'Saving output json to file...'.PHP_EOL;

        file_put_contents('storage/cucm/linecleanup/report.json', $svn_save);

        echo 'Saved to file...'.PHP_EOL;
    }
}
