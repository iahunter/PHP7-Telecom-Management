<?php

namespace App\Console\Commands\Ldap;

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Console\Command;

class TestLdapPhoneUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ldap:numberupdate {username} {number}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update User AD IPphone Field';

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
		// Create new Auth Controller for LDAP functions.
        $this->Auth = new AuthController();
		
		
		$USERNAME = $this->argument('username'); 
		$DN = $this->argument('number'); 
        $LOG = $this->Auth->changeLdapPhone($USERNAME, $DN);
		
		print_r($LOG); 
    }
}
