<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePhoneMacTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('phone_mac', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('parent')->unsigned()->index()->nullable();    // Parent Block ID
                $table->foreign('parent')->references('id')->on('phone_mac')->onDelete('cascade');        // Create foreign key and try cascade deletes

            $table->string('type')->index()->nullable();                      // simple name to reference the account by
            $table->string('status')->nullable();                      // simple name to reference the account by

            $table->string('created_by')->nullable();              // simple name to reference the account by
            $table->string('updated_by')->nullable();              // simple name to reference the account by
            $table->string('deleted_by')->nullable();              // simple name to reference the account by

            $table->json('form_data')->nullable();                  // JSON Custom Field Data
            $table->json('json')->nullable();                       // JSON Custom Field Data

            $table->timestamps();                                   // Time Stamps
            $table->softDeletes();                                  // Soft Deletes
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('phone_mac');
    }
}
