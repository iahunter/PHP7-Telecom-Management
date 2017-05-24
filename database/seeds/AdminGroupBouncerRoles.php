<?php

use Illuminate\Database\Seeder;

class AdminGroupBouncerRoles extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Assign Network Engineer to Admin.
        $group = env('ADMIN_GRP');
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
            App\Calls::class,
            App\Cucmsiteconfigs::class,
            App\Cucmphoneconfigs::class,
            App\TelecomInfrastructure::class,
			App\Ping::class,
        ];

        foreach ($types as $type) {
            foreach ($tasks as $task) {
                Bouncer::allow($group)->to($task, $type);
            }
        }
    }
}
