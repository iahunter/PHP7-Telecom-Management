<?php

use Illuminate\Database\Seeder;

class BouncerRoles extends Seeder
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
            App\Cupi::class,
            App\Cucmclass::class,
            App\Calls::class,
            App\Cucmsiteconfigs::class,
            App\Cucmphoneconfigs::class,
        ];

        foreach ($types as $type) {
            foreach ($tasks as $task) {
                Bouncer::allow($group)->to($task, $type);
            }
        }

        // Assign groups who are only allowed to read and update names.
        $group = env('READ_UPDATE_GRP');

        $tasks = [
            'read',
            'update',
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

        // Assign groups who are only allowed to read
        $group = env('READ_ONLY_GRP');

        $tasks = [
            'read',
        ];

        $types = [
            App\Didblock::class,
            App\Did::class,
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
