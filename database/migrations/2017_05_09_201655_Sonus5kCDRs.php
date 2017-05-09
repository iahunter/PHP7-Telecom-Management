<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Sonus5kCDRs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sonus_cdrs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('gw_name');
            $table->string('type');
            $table->string('accounting_id');
            $table->string('gcid')->nullable();                         // Global Call ID
            $table->string('start_date')->nullable();
            $table->string('start_time')->nullable();

            $table->string('calling_name')->nullable();
            $table->string('calling_number')->nullable();
            $table->string('called_number')->nullable();
            $table->string('dialed_number')->nullable();

            $table->string('disconnect_date')->nullable();
            $table->string('disconnect_time')->nullable();

            $table->string('call_duration')->nullable();

            $table->string('disconnect_initiator')->nullable();
            $table->string('disconnect_reason')->nullable();

            $table->string('route_label')->nullable();

            $table->string('ingress_media')->nullable();
            $table->string('egress_media')->nullable();

            $table->string('ingress_trunkgrp')->nullable();
            $table->string('egress_trunkgrp')->nullable();

            $table->string('ingress_lost_ptks')->nullable();
            $table->string('egress_lost_ptks')->nullable();

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
        Schema::dropIfExists('sonus_cdrs');
    }
}
