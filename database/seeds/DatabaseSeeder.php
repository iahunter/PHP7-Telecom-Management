<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UsersTableSeeder::class);
        // $this->call(DidblockSeeder::class);
		
		
		
        echo 'Assigning Bouncer Roles to Netowork Engineering...'.PHP_EOL;
        // $this->call(BouncerRoles::class);
		$this->call(AdminGroupBouncerRoles::class);
		
		echo 'Assigning Bouncer Roles to Service Desk...'.PHP_EOL;
		$this->call(ServiceDeskBouncerRoles::class);
		
		echo 'Assigning Bouncer Roles to Field Techs...'.PHP_EOL;
		$this->call(FieldTechsBouncerRoles::class);
		
		echo 'Assigning Bouncer Roles to Exec Rights...'.PHP_EOL;
		$this->call(ExecGroupBouncerRoles::class);
		

        echo 'Importing DID List from CSV...'.PHP_EOL;
        $this->call(ImportDIDListSeeder::class);
    }
}
