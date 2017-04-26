<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AbilitiesExtendEntityid extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
		Schema::table('abilities', function (Blueprint $table) {
			$table->string('entity_id', 255)->nullable()->change();
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('abilities', function (Blueprint $table) {
			$table->integer('entity_id')->unsigned()->nullable()->change();
		});
    }
}
