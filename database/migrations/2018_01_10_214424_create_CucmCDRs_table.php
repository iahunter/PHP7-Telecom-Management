<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $table->integer('globalCallID_callId')->index();
            $table->integer('origLegCallIdentifier');

            $table->timestamp('dateTimeConnect')->index()->nullable();
            $table->timestamp('dateTimeDisconnect')->index()->nullable();
            $table->integer('duration');

            $table->string('callingPartyNumber')->index();
            $table->string('originalCalledPartyNumber')->index();
            $table->string('finalCalledPartyNumber');
            $table->string('origDeviceName');
            $table->string('destDeviceName');

            $table->string('origIpv4v6Addr');
            $table->string('destIpv4v6Addr');

            $table->string('originalCalledPartyPattern');
            $table->string('finalCalledPartyPattern');
            $table->string('lastRedirectingPartyPattern');

            $table->json('cdrraw')->nullable();

            $table->json('json')->nullable();                       // JSON Custom Field Data

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
