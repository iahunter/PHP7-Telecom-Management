<?php

namespace App\Console\Commands\Ldap;

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Console\Command;

class GetLdapUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ldap:get_user {username}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get AD User by username';

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
        $LOG = $this->Auth->getLdapUserByName($USERNAME);

        print_r($LOG);
    }
}
