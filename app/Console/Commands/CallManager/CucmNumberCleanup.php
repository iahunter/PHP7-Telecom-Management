<?php

namespace App\Console\Commands\CallManager;

use App\Did;
use App\Cupi;
use App\Didblock;
use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Http\Controllers\Auth\AuthController;

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
    protected $description = 'Runs Report - In Cucm that have no Devices assigned, No CallForwarding Acive, and No Mailbox in Cisco Unity';

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

        $this->svn = env('CUCM_SVN');

        // Create new Auth Controller for LDAP functions.
        $this->Auth = new AuthController();

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

        echo $start.PHP_EOL;

        $didblocks = \App\Didblock::where([['country_code', '=', 1]])->get();

        // Quit if no blocks found.
        if (! $didblocks || ! count($didblocks)) {
            return;
        }

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
                            if ($entry['type'] == 'Device') {
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
        $lines_with_callhandler_built = [];
        $lines_with_callhandler_built_count = 0;
        $lines_with_other_usages = [];
        $lines_with_other_usages_count = 0;

        foreach ($possible_deletes as $blockid => $line) {
            $didblock = \App\Didblock::find($blockid);
            $sitecode = $didblock->name;

            echo "Sitecode: {$sitecode}".PHP_EOL;

            foreach ($line as $uuid => $number) {
                //$uuid = $uuid[0];
                $username = false;
                try {
                    $linedetails = $this->cucm->get_object_type_by_uuid($uuid, $TYPE);

                    $mailbox_details = Cupi::findmailboxbyextension($linedetails['pattern']);
                    $mailbox = false;
                    $callhandler = false;
                    //print_r($mailbox_details);
                    if ($mailbox_details['response']['@total'] > 0) {
                        if ((isset($mailbox_details['response']['User']))) {
                            $mailbox = $mailbox_details['response']['User'];
                            $mailbox = ['Alias'            => $mailbox['Alias'],
                                        'DisplayName'      => $mailbox['DisplayName'],
                                        'FirstName'        => $mailbox['FirstName'],
                                        'LastName'         => $mailbox['LastName'],
                                        'DtmfAccessId'     => $mailbox['DtmfAccessId'],
                                        'AD_User_Found'    => false,
                                        ];

                            if (isset($mailbox['Alias']) && $mailbox['Alias']) {

                                //print_r($mailbox);

                                $username = $mailbox['Alias'];
                                //print $username.PHP_EOL;
                                try {
                                    $username = $this->Auth->getUserLdapPhone($username);
                                } catch (\Exception $e) {
                                    echo $e->getMessage();
                                }

                                if ($username) {
                                    //print_r($username).PHP_EOL;
                                    if ($username['user']) {
                                        $fulluser = $username['user'];
                                        $fulluser = explode(',', $fulluser);
                                        foreach ($fulluser as $value) {
                                            if ($value == 'OU=Disabled Users') {
                                                $username['disabled'] = true;
                                            }
                                            if (isset($username['disabled']) && $username['disabled'] != true) {
                                                $username['disabled'] = false;
                                            }
                                        }

                                        echo "Found User for Mailbox: {$username['displayname']}".PHP_EOL;
                                        $mailbox['AD_User_Found'] = $username['user'];
                                    }
                                }
                            }

                            //print_r($mailbox);
                        } else {
                            $mailbox_details = Cupi::get_callhandler_by_extension($linedetails['pattern']);

                            //print_r($mailbox_details);
                            if ($mailbox_details['response']['@total'] > 0) {
                                if (isset($mailbox_details['response']['Callhandler'])) {
                                    $callhandler = $mailbox_details['response']['Callhandler'];

                                    echo "Found Call Handler for Exension: {$callhandler['DisplayName']}".PHP_EOL;

                                    $callhandler = ['Alias'            => $callhandler['Alias'],
                                                    'DisplayName'      => $callhandler['DisplayName'],
                                                    'DtmfAccessId'     => $callhandler['DtmfAccessId'],
                                                ];
                                }
                            }
                        }
                    }

                    // Check if CFA is set.

                    $line_summary = [
                                        'uuid'                       => $linedetails['uuid'],
                                        'pattern'                    => $linedetails['pattern'],
                                        'callForwardAll'             => $linedetails['callForwardAll']['destination'],
                                        'description'                => $linedetails['description'],
                                        'associatedDevices'          => $linedetails['associatedDevices'],
                                        'mailbox'                    => $mailbox,
                                        'callhandler'                => $callhandler,
                                        'sitecode'                   => $sitecode,
                                        'usage'                      => $linedetails['usage'],
                                        ];

                    // Only look at lines that are assigned a usage value of Device.
                    if ($linedetails['usage'] == 'Device') {
                        if ($linedetails['callForwardAll']['destination'] == '') {
                            if (! $mailbox && ! $callhandler) {
                                $lines_to_delete[$linedetails['uuid']] = $line_summary;
                                $lines_to_delete_count++;
                                //print_r($lines_to_delete[$linedetails['uuid']]);
                                echo "{$linedetails['pattern']} is Ready to Delete...".PHP_EOL;

                                // Call Delete Function here...
                            }
                            if ($mailbox || $callhandler) {
                                $lines_with_mailbox_built[$linedetails['uuid']] = $line_summary;
                                $lines_with_mailbox_built_count++;
                                //print_r($lines_with_mailbox_built[$linedetails['uuid']]);
                                echo "{$linedetails['pattern']} is has a mailbox built and cannot delete...".PHP_EOL;
                            }
                            if ($callhandler) {
                                $lines_with_callhandler_built[$linedetails['uuid']] = $line_summary;
                                $lines_with_callhandler_built_count++;
                                //print_r($lines_with_callhandler_built[$linedetails['uuid']]);
                                echo "{$linedetails['pattern']} is has a callhandler built and cannot delete...".PHP_EOL;
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
                    echo 'Something got jacked up somewhere... Review try catch. '.PHP_EOL;
                    continue;
                }

                //print_r($linedetails);
            }
        }

        $results = [];

        if (count($lines_to_delete)) {
            $results['lines_to_delete'] = $lines_to_delete;
        }
        if (count($lines_with_cfa_active)) {
            $results['lines_with_cfa_active'] = $lines_with_cfa_active;
        }
        if (count($lines_with_mailbox_built)) {
            $results['lines_with_mailbox_built'] = $lines_with_mailbox_built;
        } else {
            $results['lines_with_mailbox_built'] = false;
        }
        if (count($lines_with_callhandler_built)) {
            $results['lines_with_callhandler_built'] = $lines_with_callhandler_built;
        } else {
            $results['lines_with_callhandler_built'] = false;
        }
        if (count($lines_with_other_usages)) {
            $results['lines_with_other_usages'] = $lines_with_other_usages;
        }

        // Save Site Config as JSON and upload to subversion for change tracking.
        $svn_save = json_encode($results, JSON_PRETTY_PRINT);

        echo 'Saving output json to file...'.PHP_EOL;

        file_put_contents(storage_path('cucm/linecleanup/report.json'), $svn_save);

        echo 'Saved to file...'.PHP_EOL;

        echo '###########################################################################'.PHP_EOL;

        echo "lines_to_delete_count: {$lines_to_delete_count}".PHP_EOL;
        echo "lines_with_cfa_active_count: {$lines_with_cfa_active_count}".PHP_EOL;
        echo "lines_with_mailbox_built_count: {$lines_with_mailbox_built_count}".PHP_EOL;
        echo "lines_with_callhandler_built_count: {$lines_with_callhandler_built_count}".PHP_EOL;
        echo "lines_with_other_usages_count: {$lines_with_other_usages_count}".PHP_EOL;

        $end = Carbon::now();
        echo PHP_EOL;
        echo 'Start Time: '.$start.PHP_EOL;
        echo 'End Time: '.$end.PHP_EOL;

        echo '###########################################################################'.PHP_EOL;
    }
}
