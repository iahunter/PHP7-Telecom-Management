<?php

namespace App\Console\Commands\BouncerPermissions;

use App;
use Bouncer;
use Illuminate\Console\Command;

class ReviewGroupPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bouncer:check_permissions  {username?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check User Permissions - provide optional username argument';

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

        // Check if User argument is provided.
        if ($this->argument('username')) {
            $users = App\User::where('username', $this->argument('username'))->get();
        } else {
            $users = App\User::get();
        }

        $user_permissions = [];

        foreach ($users as $user) {
            //print $user['username'].PHP_EOL;
            $user_permissions[$user['username']] = [];
            //print $user->getAbilities();
            $abilities = $user->getAbilities();
            //print_r($abilities);

            $permissions = [];

            foreach ($abilities as $ability) {

                //print_r($ability);
                //print $ability->name.PHP_EOL;
                //print $ability->entity_id.PHP_EOL;

                // Check if the permission type (read,add,update,delete) exists in the permissions array. If not create it.
                if (! array_key_exists($ability->name, $permissions)) {
                    $permissions[$ability->name] = [];
                }

                // check if the type(Class) exists in the permissions array. If not create it.
                if (! in_array($ability->entity_type, $permissions[$ability->name])) {
                    $permissions[$ability->name][] = $ability->entity_type;
                }

                // Check for specific entity ids and add them to the class they are part of. This is for Oncall permissions
                if ($ability->entity_id) {
                    //print $ability->entity_id.PHP_EOL;
                    $permissions[$ability->name][$ability->entity_type][] = $ability->entity_id;
                }
            }

            // Create User array with associated permissions.
            $user_permissions[$user['username']] = $permissions;
        }

        // Print User permissions.
        print_r($user_permissions);
    }
}
