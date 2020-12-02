<?php

namespace App\Console\Commands\BouncerPermissions;

use App;
use Bouncer;
use Illuminate\Console\Command;

class BouncerPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bouncer:assign_permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'bouncer assign permissions to groups';

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
        // We may in the future want to track these in a database so that groups can be added to roles

        // Assign roles to each of the following groups.
        $this->assignAdminGroupBouncerRoles(env('ADMIN_GRP'));
        $this->assignExecGroupBouncerRoles(env('EXECS_GRP'));
        $this->assignServiceDeskBouncerRoles(env('SERVICEDESK_GRP'));
        $this->assignServiceDeskBouncerRoles(env('SNOW_AUTOMATION'));
        $this->assignPMBouncerRoles(env('NETWORK_GRP'));

        // $this->assignFieldTechsBouncerRoles(env('FIELD_TECH_GRP'));
        // To assign more than one group we can format the groups in JSON format in the .env
        if (json_decode(env('FIELD_TECH_GRP', true))) {
            // If valid JSON
            echo 'Found multiple groups in JSON... Attempting to assign roles for each group...'.PHP_EOL;

            // Get groups from JSON .env variable.
            $groups = json_decode(env('FIELD_TECH_GRP', true));

            foreach ($groups as $group) {
                $this->assignFieldTechsBouncerRoles($group);
            }
        } else {
            // If not in JSON format then just assign the role from string.
            $this->assignFieldTechsBouncerRoles(env('FIELD_TECH_GRP'));
        }
    }

    protected function assignAdminGroupBouncerRoles($group)
    {
        // Assign Network Engineer to Admin.

        echo 'Starting Assigning Permissions to '.$group.PHP_EOL;

        $tasks = [
            'create',
            'read',
            'update',
            'delete',
        ];

        $types = [
            App\Didblock::class,
            App\Did::class,
            App\Site::class,
            App\Phone::class,
            App\Phoneplan::class,
            App\Sonus5k::class,
            App\Sonus5kCDR::class,
            App\Cupi::class,
            App\Cucmclass::class,
            App\CucmCDR::class,
            App\CucmCMR::class,
            App\Calls::class,
            App\GatewayCalls::class,
            App\Cucmsiteconfigs::class,
            App\Cucmphoneconfigs::class,
            App\TelecomInfrastructure::class,
            App\SiteMigration::class,
            App\Ping::class,
            App\PhoneMACD::class,
            \Spatie\Activitylog\Models\Activity::class, // Activity Log Permissions
        ];

        foreach ($types as $type) {
            foreach ($tasks as $task) {
                Bouncer::allow($group)->to($task, $type);
            }
        }

        echo 'Finished Assigning Permissions'.PHP_EOL;
    }

    protected function assignPMBouncerRoles($group)
    {
        // Assign Network Engineer to Admin.

        echo 'Starting Assigning Permissions to '.$group.PHP_EOL;

        $tasks = [
            'read',
        ];

        $types = [
            App\Didblock::class,
            App\Did::class,
            App\Site::class,
            App\Phone::class,
            App\Phoneplan::class,
            App\Sonus5k::class,
            App\Sonus5kCDR::class,
            App\Cupi::class,
            App\Cucmclass::class,
            App\CucmCDR::class,
            App\CucmCMR::class,
            App\Calls::class,
            App\GatewayCalls::class,
            App\Cucmsiteconfigs::class,
            App\Cucmphoneconfigs::class,
            App\TelecomInfrastructure::class,
            App\SiteMigration::class,
            App\Ping::class,
            App\PhoneMACD::class,

        ];

        foreach ($types as $type) {
            foreach ($tasks as $task) {
                Bouncer::allow($group)->to($task, $type);
            }
        }

        $tasks = [
            'create',
            'read',
            'update',
            'delete',
        ];

        $types = [
            App\Didblock::class,
            App\Did::class,
            App\Site::class,
            App\Phone::class,
            App\Phoneplan::class,
            App\Cupi::class,
            App\Cucmclass::class,
            App\PhoneMACD::class,
        ];

        foreach ($types as $type) {
            foreach ($tasks as $task) {
                Bouncer::allow($group)->to($task, $type);
            }
        }

        echo 'Finished Assigning Permissions'.PHP_EOL;
    }

    protected function assignExecGroupBouncerRoles($group)
    {
        // Assign permissions to execs for review of features.

        echo 'Starting Assigning Permissions to '.$group.PHP_EOL;

        $tasks = [
            'create',
            'read',
            'update',
            'delete',
        ];

        $types = [
            App\Didblock::class,
            App\Did::class,
            App\Site::class,
            App\Phone::class,
            App\Phoneplan::class,
            App\Sonus5k::class,
            //App\Sonus5kCDR::class,
            App\Cupi::class,
            App\Cucmclass::class,
            App\Calls::class,
            App\GatewayCalls::class,
            App\Cucmsiteconfigs::class,
            App\Cucmphoneconfigs::class,
            App\PhoneMACD::class,
        ];

        foreach ($types as $type) {
            foreach ($tasks as $task) {
                Bouncer::allow($group)->to($task, $type);
            }
        }

        echo 'Finished Assigning Permissions '.$group.PHP_EOL;
    }

    protected function assignServiceDeskBouncerRoles($group)
    {

        // Assign groups who are only allowed to read and update names.

        echo 'Starting Assigning Permissions to '.$group.PHP_EOL;

        $tasks = [
            'read',
        ];

        $types = [
            App\Didblock::class,
            App\Did::class,
            App\Site::class,
            App\Phone::class,
            App\Phoneplan::class,
            App\Cucmclass::class,
            App\Cupi::class,
            App\Calls::class,
            App\GatewayCalls::class,
            //App\Sonus5k::class,
            App\Cucmsiteconfigs::class,
            App\Cucmphoneconfigs::class,
            App\TelecomInfrastructure::class,
            App\PhoneMACD::class,
        ];

        foreach ($types as $type) {
            foreach ($tasks as $task) {
                Bouncer::allow($group)->to($task, $type);
            }
        }

        $tasks = [
            'update',
        ];

        $types = [
            App\Didblock::class,
            App\Did::class,
            App\Phone::class,
            App\Phoneplan::class,
            App\Cucmclass::class,
            App\Cupi::class,
            App\PhoneMACD::class,
        ];

        foreach ($types as $type) {
            foreach ($tasks as $task) {
                Bouncer::allow($group)->to($task, $type);
            }
        }

        $tasks = [
            'create',
        ];

        $types = [
            App\Phone::class,
            App\Phoneplan::class,
            App\PhoneMACD::class,
        ];

        foreach ($types as $type) {
            foreach ($tasks as $task) {
                Bouncer::allow($group)->to($task, $type);
            }
        }

        $tasks = [
            'delete',
        ];

        $types = [
            App\PhoneMACD::class,
        ];

        foreach ($types as $type) {
            foreach ($tasks as $task) {
                Bouncer::allow($group)->to($task, $type);
            }
        }

        echo 'Finished Assigning Permissions '.$group.PHP_EOL;
    }

    protected function assignFieldTechsBouncerRoles($group)
    {

        // Assign groups who are only allowed to read

        echo 'Starting Assigning Permissions to '.$group.PHP_EOL;

        $tasks = [
            'read',
        ];

        $types = [
            App\Didblock::class,
            App\Did::class,
            App\Site::class,
            App\Phone::class,
            App\Phoneplan::class,
            App\Cucmclass::class,
            App\Cupi::class,
            App\Calls::class,
            App\GatewayCalls::class,
            //App\Sonus5k::class,
            App\Cucmsiteconfigs::class,
            App\Cucmphoneconfigs::class,
            //App\TelecomInfrastructure::class,
            App\PhoneMACD::class,
        ];

        foreach ($types as $type) {
            foreach ($tasks as $task) {
                Bouncer::allow($group)->to($task, $type);
            }
        }

        $tasks = [
            'update',
        ];

        $types = [
            App\Didblock::class,
            App\Did::class,
            App\Phone::class,
            App\Phoneplan::class,
            App\Cucmclass::class,
            App\Cupi::class,
            App\PhoneMACD::class,
        ];

        foreach ($types as $type) {
            foreach ($tasks as $task) {
                Bouncer::allow($group)->to($task, $type);
            }
        }

        $tasks = [
            'create',
        ];

        $types = [
            App\Phone::class,
            App\Phoneplan::class,
            App\PhoneMACD::class,
        ];

        foreach ($types as $type) {
            foreach ($tasks as $task) {
                Bouncer::allow($group)->to($task, $type);
            }
        }

        $tasks = [
            'delete',
        ];

        $types = [
            App\PhoneMACD::class,
        ];

        foreach ($types as $type) {
            foreach ($tasks as $task) {
                Bouncer::allow($group)->to($task, $type);
            }
        }

        echo 'Finished Assigning Permissions'.PHP_EOL;
    }
}
