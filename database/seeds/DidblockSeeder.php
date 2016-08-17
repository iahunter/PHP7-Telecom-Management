<?php

use Illuminate\Database\Seeder;

class DidblockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
		// Create Seed Data for DID Block table
        /*DB::table('did_block')->insert([
            'country_code' => 1,
			'name' => str_random(10),
			'carrier' => str_random(10),
			'start' => str_random(10),
			'end' => str_random(10),
			'comment' => str_random(10),

        ]);*/
		DB::table('did_block')->insert([
            'country_code' => 1,
			'name' => "TEST1",
			'carrier' => str_random(10),
			'start' => 4025551000,
			'end' => 4025551999,
			'comment' => str_random(10),

        ]);
		DB::table('did_block')->insert([
            'country_code' => 1,
			'name' => str_random(10),
			'carrier' => str_random(10),
			'start' => 4025552000,
			'end' => 4025552999,
			'comment' => str_random(10),

        ]);
		DB::table('did_block')->insert([
            'country_code' => 1,
			'name' => str_random(10),
			'carrier' => str_random(10),
			'start' => 4025553000,
			'end' => 4025553999,
			'comment' => str_random(10),

        ]);
    }
}

/*
            $table->increments('id');
            $table->integer('block_id')->unsigned();	// Parent Block ID
            $table->string('name');           			// Name
			$table->bigInteger('number');        		// Phone Number
			$table->string('status');            		// Status - Active/Reserved/Available
			$table->string('system_id');            	// Future - System ID - CUCM/Lync ID
			$table->json('assignements');           	// JSON Custom Field Data
            $table->timestamps();						// Time Stamps
            $table->softDeletes();            			// keep deactivated certificates in the table
*/
