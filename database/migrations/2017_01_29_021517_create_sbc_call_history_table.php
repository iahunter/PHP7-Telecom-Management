<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSbcCallHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Site Code Table
		Schema::create('sbc_calls', function (Blueprint $table) {
		$table->increments('id');
		$table->string('name')->nullable();              			// SBC Name
		$table->integer('totalCalls');                				// Calls
		$table->json('stats')->nullable();            				// JSON Details Custom Field Data
		$table->timestamps();                           			// Time Stamps
		$table->softDeletes();                          			// Soft Deletes
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('sbc_calls');
    }
}
