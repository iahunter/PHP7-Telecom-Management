<?php

use App\Didblock;
use Illuminate\Database\Seeder;

//use App\Didblock;

class DidblockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $count = 0;
        $start = 1234560000;
        $end = 1234560009;

        // Create DIDs until the count is excedded.
        while ($count < 10) {
            $count++;

            /* Insert into DB.
            DB::table('did_block')->insert([
            ['country_code' => 1, 'name' => 'TEST DID Block '.str_random(10), 'carrier' => str_random(10), 'start' => $start, 'end' => $end, 'comment' => str_random(10), 'type' => 'public'],
            ]);
            */

            //Insert using Model.
            Didblock::create(['country_code' => 1,
                                'name'       => 'TEST DID Block '.str_random(10),
                                'carrier'    => str_random(10),
                                'start'      => $start,
                                'end'        => $end,
                                'comment'    => str_random(10),
                                'type'       => 'public',
                                ]
            );

            $start = $start + 10;
            $end = $end + 10;
        }

        // Create Seed Data for DID Block table
        /*
        DB::table('did_block')->insert(array(
        array('country_code' => 1,'name' => "TEST1",'carrier' => str_random(10),'start' => 4025541000,'end' => 4025541999,'comment' => str_random(10),),
        array('country_code' => 1,'name' => "TEST2",'carrier' => str_random(10),'start' => 4025542000,'end' => 4025545999,'comment' => str_random(10),),
        array('country_code' => 1,'name' => "TEST3",'carrier' => str_random(10),'start' => 4025543000,'end' => 4025545999,'comment' => str_random(10),),
        array('country_code' => 1,'name' => "TEST4",'carrier' => str_random(10),'start' => 4025544000,'end' => 4025545999,'comment' => str_random(10),),
        array('country_code' => 1,'name' => "TEST5",'carrier' => str_random(10),'start' => 4025545000,'end' => 4025545999,'comment' => str_random(10),),
        ));

        $didblock = factory(App\Didblock::class)->make([
            'country_code' => 1,'name' => "TESTING",'carrier' => str_random(10),'start' => 4025531000,'end' => 4025531999,'comment' => str_random(10),
        ]);

        factory(App\Didblock::class, 50)->create()->each(function($didblock) {
        $didblock->posts()->save(factory(App\Didblock::class)->make());
        });
        */
    }
}
