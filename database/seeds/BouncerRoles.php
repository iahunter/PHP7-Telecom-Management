<?php

use Illuminate\Database\Seeder;
use App\Didblock;

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
		];

		foreach($types as $type){
			foreach($tasks as $task) {
				Bouncer::allow($group)->to($task, $type);
			}
		}

    }
}
