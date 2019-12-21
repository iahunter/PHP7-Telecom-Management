<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLastRegisteredFieldToCucmphoneTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cucmphone', function (Blueprint $table) {
			$table->timestamp('last_registered')->nullable();
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cucmphone', function (Blueprint $table) {
			$table->dropColumn('last_registered');
		});
    }
}
