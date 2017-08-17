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
            $table->string('name');                     // Name
            $table->string('carrier')->nullable();      // Carrier Name
            $table->string('country_code');             // simple name to reference the account by
            $table->bigInteger('start');                // Start of Block
            $table->bigInteger('end');                  // End of Block
            $table->string('type');                     // Public or Private Number
            $table->boolean('reserved')->nullable();                 // Reserved Status for Automation Only Assignment
            $table->text('comment')->nullable();                    // Comment
            $table->json('json')->nullable();                       // JSON Custom Field Data
            $table->string('created_by')->nullable();              // simple name to reference the account by
            $table->string('updated_by')->nullable();              // simple name to reference the account by
            $table->string('deleted_by')->nullable();              // simple name to reference the account by
            $table->timestamps();                       // Time Stamps
            $table->softDeletes();                      // Soft Deletes
        });
        //DB::update('ALTER TABLE acme_accounts AUTO_INCREMENT = 10;');

        Schema::create('did', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('parent')->unsigned()->index();    // Parent Block ID
                $table->foreign('parent')->references('id')->on('did_block')->onDelete('cascade');        // Create foreign key and try cascade deletes

            $table->string('name')->nullable();                       // Name
            $table->string('country_code');             // simple name to reference the account by
            $table->bigInteger('number');                // Phone Number
            $table->string('status');                    // Status - Active/Reserved/Available
            $table->string('system_id')->nullable();                // Future - System ID - CUCM/Lync ID
            $table->json('assignments')->nullable();                   // JSON Custom Field Data
            $table->json('mailbox')->nullable();                   // JSON Custom Field Data
            $table->string('created_by')->nullable();              // simple name to reference the account by
            $table->string('updated_by')->nullable();              // simple name to reference the account by
            $table->string('deleted_by')->nullable();              // simple name to reference the account by
            $table->timestamps();                        // Time Stamps
            $table->softDeletes();                        // keep deactivated certificates in the table

            // 1:many account->certificates relationship
            //$table->foreign('parent')->references('id')->on('did_block')
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
