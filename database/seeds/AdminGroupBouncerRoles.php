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
        $group = 'CN=IMNetworkEngineering,OU=Groups,OU=Kiewit,DC=KIEWITPLAZA,DC=com';
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
        ];

        foreach ($types as $type) {
            foreach ($tasks as $task) {
                Bouncer::allow($group)->to($task, $type);
            }
        }
    }
}
