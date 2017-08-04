<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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
            $table->string('type')->nullable();                      // simple name to reference the account by
            $table->json('form_data')->nullable();                  // JSON Custom Field Data
            $table->json('json')->nullable();                       // JSON Custom Field Data
            $table->string('status')->nullable();                      // simple name to reference the account by
            $table->string('created_by')->nullable();              // simple name to reference the account by
            $table->string('updated_by')->nullable();              // simple name to reference the account by
            $table->string('deleted_by')->nullable();              // simple name to reference the account by
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
