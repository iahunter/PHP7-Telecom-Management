<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSitePlanning extends Migration
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
            //$table->text('type')->nullable();                  // Comment
            $table->text('trunking')->nullable();           // Comment
            $table->text('e911')->nullable();                  // Comment
            $table->text('srstip')->nullable();              // Comment
            $table->json('h323ip')->nullable();                // JSON Details Custom Field Data
            $table->text('didrange')->nullable();           // JSON Details Custom Field Data
            $table->text('npa')->nullable();                // JSON Details Custom Field Data
            $table->text('nxx')->nullable();                // JSON Details Custom Field Data
            $table->text('timezone')->nullable();           // JSON Details Custom Field Data
            $table->json('didblocks')->nullable();           // JSON Details ID of DID Blocks used for the site.
            $table->integer('extlen')->nullable();           // JSON Details ID of DID Blocks used for the site.
            $table->text('operator')->nullable();              // Comment
            $table->json('details')->nullable();            // JSON Details Custom Field Data
            //$table->string('createdby');            // simple name to reference the account by
            //$table->string('updatedby');            // simple name to reference the account by
            $table->timestamps();                           // Time Stamps
            $table->softDeletes();                          // Soft Deletes
        });

        // Child Phone Plan - parent site
        Schema::create('phoneplan', function (Blueprint $table) {
            $table->increments('id');
            /*
            $table->integer('parent')->unsigned()->index();    // Parent Block ID
                $table->foreign('parent')->references('id')->on('site')->onDelete('cascade');        // Create foreign key and try cascade deletes
            */
            $table->integer('site')->unsigned()->index();    // Parent Block ID
                $table->foreign('site')->references('id')->on('site')->onDelete('cascade');        // Create foreign key and try cascade deletes

            $table->string('name');                        // Name
            $table->string('description')->nullable();                 // simple name to reference the account by
            $table->string('status')->nullable();           // simple name to reference the account by
            //$table->boolean('deployed')->nullable();             // Deployed Status - true/false
            $table->string('system_id')->nullable();
            $table->string('notes')->nullable();            // Future - System ID - CUCM/Lync ID
            $table->string('language')->nullable();            // Future - System ID - CUCM/Lync ID
			$table->string('employee_vm_user_template')->nullable();            // CUPI User Template
			$table->string('nonemployee_vm_user_template')->nullable();            // CUPI User Template
            $table->json('json')->nullable();            // JSON Details Custom Field Data
            //$table->string('createdby');            // simple name to reference the account by
            //$table->string('updatedby');            // simple name to reference the account by
            $table->timestamps();                        // Time Stamps
            $table->softDeletes();                        // keep deactivated certificates in the table
        });

        // Child Phone - parent site
        Schema::create('phone', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('phoneplan')->unsigned()->index();    // Parent Block ID
                $table->foreign('phoneplan')->references('id')->on('phoneplan')->onDelete('cascade');        // Create foreign key and try cascade deletes
            $table->integer('site')->unsigned()->index();    // Parent Block ID
                $table->foreign('site')->references('id')->on('site')->onDelete('cascade');        // Create foreign key and try cascade deletes

            $table->string('name');                                    // Name
            $table->string('device');                                 // simple name to reference the account by
            $table->string('firstname');                            // simple name to reference the account by
            $table->string('lastname');                             // simple name to reference the account by
            $table->string('username')->nullable();                 // simple name to reference the account by
            $table->string('dn');                                     // Directory Number
            $table->string('language');                                // Directory Number
            $table->boolean('phonetemplate')->nullable();           // Maybe used in the future.
            $table->string('voicemail');                            // Voicemail - true/false
			$table->string('vm_user_template')->nullable();            // CUPI User Template
            $table->boolean('deployed')->nullable();                 // Deployed Status - true/false
            $table->boolean('provisioned')->nullable();                 // Deployed Status - true/false
            $table->json('assignments')->nullable();                   // JSON Custom Field Data
            $table->string('system_id')->nullable();
            $table->string('notes')->nullable();            // Future - System ID - CUCM/Lync ID
            //$table->string('createdby');            // simple name to reference the account by
            //$table->string('updatedby');            // simple name to reference the account by
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
        Schema::drop('phoneplan');
        Schema::drop('site');
    }
}
