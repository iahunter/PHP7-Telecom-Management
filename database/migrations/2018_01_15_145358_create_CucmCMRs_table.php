<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $table->integer('globalCallID_callId')->index();
            $table->timestamp('dateTimeStamp')->index()->nullable();
            $table->string('directoryNum');
            $table->string('callIdentifier');

            $table->string('directoryNumPartition');
            $table->string('deviceName')->index();

            $table->string('varVQMetrics');
            $table->integer('numberPacketsSent')->nullable();
            $table->integer('numberPacketsReceived')->index()->nullable();
            $table->integer('jitter')->nullable();
            $table->integer('numberPacketsLost')->nullable();
            $table->float('packetLossPercent', 8, 2)->index()->nullable();

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
