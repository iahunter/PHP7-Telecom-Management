<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCucmPhoneStatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		Schema::create('cucmphonestats', function (Blueprint $table) {
			$table->increments('id');
			$table->string('type')->nullable();
			$table->integer('total')->nullable();
			$table->integer('registered')->nullable();				
			$table->json('stats')->nullable();                      // JSON Custom Field Data
			$table->json('json')->nullable();                       // JSON Custom Field Data
			$table->timestamps();                       			// Time Stamps
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('cucmphonestats');
    }
}
