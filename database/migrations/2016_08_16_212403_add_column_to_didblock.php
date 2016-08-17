<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnToDidblock extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('did_block', function (Blueprint $table) {
            // Add new the column to table
			$table->text('comment');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('did_block', function (Blueprint $table) {
            // Drop the column 
			$table->dropColumn('comment');
        });
    }
}
