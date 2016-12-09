<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBulkPhoneProvisioning extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Site Code Table
        Schema::create('sitecode', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');                            // Name
            $table->text('comment')->nullable();            // Comment
            $table->json('json')->nullable();                // JSON Custom Field Data
            $table->timestamps();                           // Time Stamps
            $table->softDeletes();                          // Soft Deletes
        });

        // Child Phone Planner
        Schema::create('phones', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('sitecode')->unsigned()->index();    // Parent Block ID
                $table->foreign('parent')->references('id')->on('site')->onDelete('cascade');        // Create foreign key and try cascade deletes

            $table->string('name')->nullable();    // Name
            $table->string('type');                 // simple name to reference the account by
            $table->string('firstname');            // simple name to reference the account by
            $table->string('lastname');             // simple name to reference the account by
            $table->string('username');             // simple name to reference the account by
            $table->string('dn');                     // Directory Number
            $table->string('language');                // Directory Number
            $table->boolean('voicemail');            // Voicemail - true/false
            $table->boolean('deployed');            // Deployed Status - true/false
            $table->boolean('provisioned');                // Deployed Status - true/false
            $table->json('assignments')->nullable();                   // JSON Custom Field Data
            $table->string('system_id')->nullable();                // Future - System ID - CUCM/Lync ID
            $table->timestamps();                        // Time Stamps
            $table->softDeletes();                        // keep deactivated certificates in the table
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
        Schema::drop('sitecode');
        Schema::drop('phones');
    }
}
