<?php

namespace App\Console\Commands\UnityConnection;

use App\Did;
use App\Cupi;
use App\Didblock;
use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Http\Controllers\Auth\AuthController;

class DidNumberUpdatesByMailboxNumber extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cisco_unity_connection:didscan';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Did Database info with Mailbox User';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {

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

        $updated_did_mailbox = 0;
        $updated_ad_ipphone = 0;
        $updated_did_mailbox_callhandler = 0;
		
        foreach ($didblocks as $didblock) {
            $didblock_count++;

            echo "Block ID: {$didblock->id}".PHP_EOL;

            echo 'Block Count: '.$didblock_count.' of '.count($didblocks).PHP_EOL;
            $sitecode = $didblock->name;

            // Get the DID records matching $npanxx.'%' - Only Valid for NANP Numbers
            if (\App\Did::where([['parent', '=', $didblock->id]])->count()) {
                $dids = \App\Did::where([['parent', '=', $didblock->id]])->get();

                //$dids = json_decode(json_encode($dids, true));

                $count = 0;
                foreach ($dids as $did) {
                    $count++;

                    echo 'Did '.$count.' of '.count($dids).": {$did->number} ".PHP_EOL;

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

                            if (! isset($mailbox['FirstName'])) {
                                $mailbox['FirstName'] = '';
                            }
                            if (! isset($mailbox['LastName'])) {
                                $mailbox['LastName'] = '';
                            }

                            $mailbox = ['Alias'                 => $mailbox['Alias'],
                                            'DisplayName'       => $mailbox['DisplayName'],
                                            'FirstName'         => $mailbox['FirstName'],
                                            'LastName'          => $mailbox['LastName'],
                                            'DtmfAccessId'      => $mailbox['DtmfAccessId'],
                                            'AD User'           => false,
                                            ];

                            // Update the Did Database
                            $did->mailbox = ['User' => $mailbox];
                            $did->save();

                            $updated_did_mailbox++;

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

                                    }
                                }
                            }

                            //print_r($mailbox);
                        } else {
                            echo 'No Mailbox Found... Looking for a Call Handler'.PHP_EOL;
                            $mailbox_details = Cupi::get_callhandler_by_extension($linedetails['pattern']);

                            //print_r($mailbox_details);
                            if ($mailbox_details['response']['@total'] > 0) {
                                if (isset($mailbox_details['response']['Callhandler'])) {
                                    $callhandler = $mailbox_details['response']['Callhandler'];

                                    echo "Found Call Handler for Exension: {$callhandler['DisplayName']}".PHP_EOL;

                                    $callhandler = ['Alias'                => $callhandler['Alias'],
                                                        'DisplayName'      => $callhandler['DisplayName'],
                                                        'DtmfAccessId'     => $callhandler['DtmfAccessId'],
                                                    ];

                                    // Update the Did Database
                                    $did->mailbox = ['Callhandler' => $mailbox];
                                    $did->save();

                                    $updated_did_mailbox_callhandler++;
                                }
                            }
                        }
                    } else {

                            // If not mailbox clear out if something is set.
                        if ($did->mailbox) {
                            $did->mailbox = null;
                            $did->save();
                        }
                    }
                    //}
                }
            }
        }

        echo '###########################################################################'.PHP_EOL;

        echo "Updated Mailboxes: {$updated_did_mailbox}".PHP_EOL;
        echo "Updated Mailbox with CallHandler: {$updated_did_mailbox_callhandler}".PHP_EOL;
        echo "Updated AD IP Phones: {$updated_ad_ipphone}".PHP_EOL;

        $end = Carbon::now();
        echo PHP_EOL;
        echo 'Start Time: '.$start.PHP_EOL;
        echo 'End Time: '.$end.PHP_EOL;

        echo '###########################################################################'.PHP_EOL;
    }
}
