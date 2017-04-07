<?php

use Illuminate\Database\Seeder;

class FieldTechsBouncerRoles extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        // Assign groups who are only allowed to read
        $group = env('FIELD_TECH_GRP');

        $tasks = [
            'read',
        ];

        $types = [
            App\Didblock::class,
            App\Did::class,
            App\Phone::class,
            App\Phoneplan::class,
			App\Calls::class,
        ];

        foreach ($types as $type) {
            foreach ($tasks as $task) {
                Bouncer::allow($group)->to($task, $type);
            }
        }
    }
}
