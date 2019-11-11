<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGatewayCallsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Site Code Table
        Schema::create('gateway_calls', function (Blueprint $table) {
            $table->timestamps();                                       // Time Stamps
            $table->increments('id');
            $table->integer('totalCalls')->index();                                // Calls
            $table->json('stats')->nullable();                            // JSON Details Custom Field Data
            $table->softDeletes();                                      // Soft Deletes
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('gateway_calls');
    }
}
