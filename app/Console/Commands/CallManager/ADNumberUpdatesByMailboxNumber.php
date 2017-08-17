<?php

namespace App\Console\Commands\CallManager;

use App\Did;
use App\Cupi;
use App\Didblock;
use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Http\Controllers\Auth\AuthController;

class ADNumberUpdatesByMailboxNumber extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ldap:update_ipphone_from_mailbox';

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

        echo $start;

        $didblocks = \App\Didblock::where([['country_code', '=', 1]])->get();

        $didblocks_count = count($didblocks);
        $didblock_count = 0;

        $possible_deletes = [];
        foreach ($didblocks as $didblock) {
            $didblock_count++;
            echo 'Block Count: '.$didblock_count.' of '.count($didblocks).PHP_EOL;
            $sitecode = $didblock->name;

            // Get the DID records matching $npanxx.'%' - Only Valid for NANP Numbers
            if (\App\Did::where([['parent', '=', $didblock->id]])->count()) {
                $dids = \App\Did::where([['parent', '=', $didblock->id]])->get();

                //$dids = json_decode(json_encode($dids, true));

                $count = 0;
                foreach ($dids as $did) {
                    $count++;
                    echo 'Did '.$count.' of '.count($dids).PHP_EOL;
                    if ($did->status == 'inuse') {

                         // If it is inuse - Go see if it has a mailbox and update the mailbox field.
                        try {
                            $mailbox_details = Cupi::findmailboxbyextension($did->number);
                        } catch (\Exception $e) {
                            echo $e->getMessage();
                            continue;
                        }
                        $mailbox = false;
                        $callhandler = false;
                        //print_r($mailbox_details);
                        if ($mailbox_details['response']['@total'] > 0) {
                            if ((isset($mailbox_details['response']['User']))) {
                                $mailbox = $mailbox_details['response']['User'];
                                $mailbox = ['Alias'             => $mailbox['Alias'],
                                            'DisplayName'       => $mailbox['DisplayName'],
                                            'FirstName'         => $mailbox['FirstName'],
                                            'LastName'          => $mailbox['LastName'],
                                            'DtmfAccessId'      => $mailbox['DtmfAccessId'],
                                            'AD User'           => false,
                                            ];

                                // Update the Did Database
                                $did->mailbox = ['User' => $mailbox];
                                $did->save();
                                if (isset($mailbox['Alias']) && $mailbox['Alias']) {

                                    //print_r($mailbox);

                                    $username = $mailbox['Alias'];

                                    //print $username.PHP_EOL;
                                    try {
                                        $ldap_user = $this->Auth->getUserLdapPhone($username);
                                    } catch (\Exception $e) {
                                        echo $e->getMessage();
                                        continue;
                                    }

                                    //print_r($ldap_user);

                                    if ($ldap_user) {
                                        //print_r($username).PHP_EOL;
                                        if ($ldap_user['user']) {
                                            $fulluser = $ldap_user['user'];
                                            $fulluser = explode(',', $fulluser);
                                            foreach ($fulluser as $value) {
                                                if ($value == 'OU=Disabled Users') {
                                                    $ldap_user['disabled'] = true;
                                                }
                                                if (isset($ldap_user['disabled']) && $ldap_user['disabled'] != true) {
                                                    $ldap_user['disabled'] = false;
                                                }
                                            }

                                            echo "Found User for Mailbox: {$ldap_user['displayname']}".PHP_EOL;

                                            $mailbox['AD User'] = $ldap_user['userprincipalname'];

                                            // Update the Did Database
                                            $did->mailbox = ['User' => $mailbox];
                                            $did->save();

                                            //print_r($did->mailbox);

                                            if ($ldap_user['ipphone'] != $mailbox['DtmfAccessId']) {
                                                $DN = $mailbox['DtmfAccessId'];
                                                $USERNAME = $ldap_user['userprincipalname'];

                                                // If the IP Phone Field doesn't match what is in Unity Connection - Update it.
                                                try {
                                                    $update = $this->Auth->changeLdapPhone($USERNAME, $DN);
                                                    echo "Updated User IP Phone Field from {$ldap_user['ipphone']} to {$DN}".PHP_EOL;
                                                } catch (\Exception $e) {
                                                    echo $e->getMessage();
                                                    continue;
                                                }
                                            }
                                        }
                                    }
                                }

                                //print_r($mailbox);
                            } else {
                                echo 'No Mailbox Found... Looking for a Call Handler';
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

                                        // Update the Did Database
                                        $did->mailbox = ['Callhandler' => $mailbox];
                                        $did->save();
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

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
