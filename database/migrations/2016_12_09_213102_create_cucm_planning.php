<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCucmPlanning extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Site Code Table
        Schema::create('site', function (Blueprint $table) {
            $table->increments('id');
            $table->string('sitecode');                         // Name
            $table->text('comment')->nullable();            // Comment
			$table->text('system_id')->nullable();          // Comment
			$table->text('type')->nullable();          		// Comment
			$table->text('srstip')->nullable();          	// Comment
			$table->json('h323ip')->nullable();            	// JSON Details Custom Field Data
			$table->text('npa')->nullable();            	// JSON Details Custom Field Data
			$table->text('nxx')->nullable();            	// JSON Details Custom Field Data
			$table->text('timezone')->nullable();           // JSON Details Custom Field Data
			$table->json('didrange')->nullable();           // JSON Details Custom Field Data
			$table->text('operator')->nullable();          	// Comment
            $table->json('details')->nullable();            // JSON Details Custom Field Data
            $table->timestamps();                           // Time Stamps
            $table->softDeletes();                          // Soft Deletes
        });

        // Child Phone Planner
        Schema::create('phone', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('parent')->unsigned()->index();    // Parent Block ID
                $table->foreign('parent')->references('id')->on('site')->onDelete('cascade');        // Create foreign key and try cascade deletes

            $table->string('name');    					// Name
            $table->string('device');                 // simple name to reference the account by
            $table->string('firstname');            // simple name to reference the account by
            $table->string('lastname');             // simple name to reference the account by
            $table->string('username');             // simple name to reference the account by
            $table->string('dn');                     // Directory Number
            $table->string('language');                // Directory Number
            $table->string('voicemail');            // Voicemail - true/false
            $table->boolean('deployed')->nullable();             // Deployed Status - true/false
            $table->boolean('provisioned')->nullable();                 // Deployed Status - true/false
            $table->json('assignments')->nullable();                   // JSON Custom Field Data
            $table->string('system_id')->nullable();
			$table->string('notes')->nullable();			// Future - System ID - CUCM/Lync ID
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
		Schema::drop('phone');
        Schema::drop('site');
        
    }
}
