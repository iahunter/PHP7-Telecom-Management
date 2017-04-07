<?php

use Illuminate\Database\Seeder;

class ServiceDeskBouncerRoles extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        // Assign groups who are only allowed to read and update names.
        $group = env('SERVICEDESK_GRP');

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
            //App\Sonus5k::class,
            App\Cucmsiteconfigs::class,
            App\Cucmphoneconfigs::class,
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
        ];

        foreach ($types as $type) {
            foreach ($tasks as $task) {
                Bouncer::allow($group)->to($task, $type);
            }
        }
    }
}
