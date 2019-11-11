<?php

namespace App\Console\Commands\CallManager;

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Cucm;
use App\Http\Controllers\Cucmphone;
use Illuminate\Console\Command;

class LdapUserUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'callmanager:users-upadate-ldap-ipphone';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates User IP Phone Field in LDAP from app/Console/Commands/CallManager/Imports/Phones.txt';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->cucmphone = new Cucmphone();
        $this->cucm = new Cucm();

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
        // Include the phones.php $phones variable to import phones.
        require __DIR__.'/Imports/Phones.txt';
        echo $phones;

        $phones = $this->cucmphone->phones_string_to_array($phones);

        $ERRORS = [];
        $ARRAY = [];
        foreach ($phones as $PHONE) {
            $username = $PHONE['username'];
            $phonenumber = $PHONE['dn'];
            echo 'Updating User..'.PHP_EOL;
            print_r($PHONE);
            if (! empty($username)) {
                try {
                    $result = $this->Auth->changeLdapPhone($username, $phonenumber);
                    print_r($result);
                    $ARRAY[] = $result;
                } catch (\Exception $e) {
                    $ERRORS[] = 'Username not found: '.$e->getMessage();
                    echo 'Username not found: '.$e->getMessage().PHP_EOL;
                }
            } else {
                echo 'No username set... Skipping...';
            }
        }

        echo PHP_EOL.'Starting CUCM LDAP Sync Process...'.PHP_EOL;
        $sync = $this->cucm->start_ldap_sync();
        print_r($sync);

        print_r($ERRORS);
    }
}
