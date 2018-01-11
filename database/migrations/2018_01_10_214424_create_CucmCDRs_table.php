<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCucmCDRsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cucm_cdrs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('globalCallID_callId');
            $table->integer('dateTimeConnect');
            $table->integer('dateTimeDisconnect');
            $table->integer('duration');

            $table->string('callingPartyNumber');
            $table->string('originalCalledPartyNumber');
            $table->string('finalCalledPartyNumber');
            $table->string('origDeviceName');
            $table->string('destDeviceName');

            $table->string('origIpv4v6Addr');
            $table->string('destIpv4v6Addr');

            $table->string('originalCalledPartyPattern');
            $table->string('finalCalledPartyPattern');
            $table->string('lastRedirectingPartyPattern');

            $table->json('raw')->nullable();
            $table->json('cdr_json')->nullable();                       // JSON Custom Field Data

            $table->timestamps();                                   // Time Stamps
        });

        //DB::update('ALTER TABLE acme_accounts AUTO_INCREMENT = 10;');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cucm_cdrs');
    }
}
