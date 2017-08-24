<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCucmConfigs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Site Code Table
        Schema::create('cucmsite', function (Blueprint $table) {
            $table->increments('id');
            $table->string('sitecode');                         // Name
            $table->string('trunking')->nullable();           // Comment
            $table->string('e911')->nullable();                  // Comment
            $table->json('sitesummary')->nullable();           // JSON Details ID of DID Blocks used for the site.
            $table->json('sitedetails')->nullable();           // JSON Details ID of DID Blocks used for the site.
            $table->timestamps();                           // Time Stamps
            $table->softDeletes();                          // Soft Deletes
        });

        // Child Phone Plan - parent site
        Schema::create('cucmphone', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('devicepool')->nullable();
            $table->string('model')->nullable();
            $table->string('ownerid')->nullable();
            $table->string('css')->nullable();
            $table->string('erl')->nullable();            // Future E911 Integration
            $table->string('ipv4address')->nullable();        // Future RISDB API

            $table->string('risdb_ipv4address')->nullable();            // RISDB IP Address
            $table->string('risdb_registration_status')->nullable();    // RISDB Status
            $table->json('config')->nullable();                // JSON Details Custom Field Data
            $table->json('lines')->nullable();              // JSON Details Custom Field Data
            $table->timestamps();                            // Time Stamps
            $table->softDeletes();                            // keep deactivated certificates in the table
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::drop('cucmsite');
        Schema::drop('cucmphone');
    }
}
