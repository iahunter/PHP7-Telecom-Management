<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePhoneNumbersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('did_block', function (Blueprint $table) {
            $table->increments('id');
            $table->string('country_code');             // simple name to reference the account by
            $table->string('name');                     // Name
            $table->string('carrier');                  // Carrier Name
            $table->bigInteger('start');                // Start of Block
            $table->bigInteger('end');                  // End of Block
            $table->string('type');                     // Public or Private Number
            $table->string('reserved');                 // Reserved Status for Automation Only Assignment
            $table->text('comment');                    // Comment
            $table->json('json');                       // JSON Custom Field Data
            $table->timestamps();                       // Time Stamps
            $table->softDeletes();                      // Soft Deletes
        });
        //DB::update('ALTER TABLE acme_accounts AUTO_INCREMENT = 10;');

        Schema::create('did', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('didblock_id')->unsigned()->index();    // Parent Block ID
                $table->foreign('didblock_id')->references('id')->on('did_block')->onDelete('cascade');        // Create foreign key and try cascade deletes

            $table->string('name');                       // Name
            $table->bigInteger('number');                // Phone Number
            $table->string('status');                    // Status - Active/Reserved/Available
            $table->string('system_id');                // Future - System ID - CUCM/Lync ID
            $table->json('assignments');                   // JSON Custom Field Data
            $table->timestamps();                        // Time Stamps
            $table->softDeletes();                        // keep deactivated certificates in the table

            // 1:many account->certificates relationship
            //$table->foreign('didblock_id')->references('id')->on('did_block')
        });
        //DB::update('ALTER TABLE acme_certificates AUTO_INCREMENT = 10;');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::drop('did');
        Schema::drop('did_block');
    }
}
