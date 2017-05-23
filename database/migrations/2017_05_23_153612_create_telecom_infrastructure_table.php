<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTelecomInfrastructureTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('telecom_infrastructure', function (Blueprint $table) {
            $table->increments('id');
            $table->string('hostname');
			$table->text('comment')->nullable();
            $table->string('role')->nullable();
            $table->string('manufacture')->nullable();           
            $table->string('model')->nullable();             
            $table->integer('software_version')->nullable();             
            $table->string('ip_address')->nullable();                 
			$table->string('mgmt_url')->nullable(); 
			$table->string('location')->nullable();
            $table->json('json')->nullable();                       // JSON Custom Field Data
            $table->string('created_by')->nullable();              // simple name to reference the account by
            $table->string('updated_by')->nullable();              // simple name to reference the account by
            $table->string('deleted_by')->nullable();              // simple name to reference the account by
            $table->timestamps();                       // Time Stamps
            $table->softDeletes();                      // Soft Deletes
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('telecom_infrastructure');
    }
}
