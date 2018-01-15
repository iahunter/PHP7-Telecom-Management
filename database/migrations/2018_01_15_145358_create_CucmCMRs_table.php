<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCucmCMRsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cucm_cmrs', function (Blueprint $table) {
            $table->increments('id');
			$table->integer('globalCallID_callId');
            $table->timestamp('dateTimeStamp')->nullable();
            $table->string('directoryNum');
            $table->string('callIdentifier');
			
			$table->string('directoryNumPartition');
            $table->string('deviceName');
			
			$table->string('varVQMetrics');
			$table->integer('numberPacketsSent')->nullable();
			$table->integer('numberPacketsReceived')->nullable();
			$table->integer('jitter')->nullable();
			$table->integer('numberPacketsLost')->nullable();
			$table->float('packetLossPercent', 8, 2)->nullable();

			$table->json('cmrraw')->nullable();
			
            $table->json('json')->nullable();                       // JSON Custom Field Data

            $table->timestamps();                                   // Time Stamps
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cucm_cmrs');
    }
}
