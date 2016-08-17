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
            $table->string('country_code');         	// simple name to reference the account by
            $table->string('name');        				// Name
			$table->string('carrier');        			// Carrier Name
			$table->bigInteger('start');        		// Start of Block
			$table->bigInteger('end');        			// End of Block
			$table->text('comment');        			// Comment
            $table->timestamps();						// Time Stamps
            $table->softDeletes();            			// Soft Deletes
        });
        //DB::update('ALTER TABLE acme_accounts AUTO_INCREMENT = 10;');

        Schema::create('did', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('block_id')->unsigned();	// Parent Block ID
            $table->string('name');           			// Name
			$table->bigInteger('number');        		// Phone Number
			$table->json('assignements');           	// JSON Custom Field Data
			$table->string('status');            		// Status - Active/Reserved/Available
            $table->timestamps();						// Time Stamps
            $table->softDeletes();            			// keep deactivated certificates in the table

            // 1:many account->certificates relationship
            $table->foreign('block_id')->references('id')->on('did_block');
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
