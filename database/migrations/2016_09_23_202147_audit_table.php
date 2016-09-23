<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AuditTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('audit', function (Blueprint $table) {
            $table->increments('id');
			
			$table->integer('user_id')->unsigned()->index();    // Parent Block ID
                $table->foreign('user_id')->references('id')->on('users');        // Create foreign key and try cascade deletes
			
			$table->string('file');                    // Model Name
            $table->string('method');                    // Model Name
			$table->string('message');                    // Model Name
            $table->json('previous');                   // JSON Custom Field Data
			//$table->json('current');                    // JSON Custom Field Data
            $table->timestamps();                       // Time Stamps
            //$table->softDeletes();                      // Soft Deletes
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('audit');
    }
}
