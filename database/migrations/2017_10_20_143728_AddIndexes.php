<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndexes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Site Code Table
        Schema::table('gateway_calls', function (Blueprint $table) {
            $table->index('created_at');                                       // Time Stamps
        });

        // Site Code Table
        Schema::table('sbc_calls', function (Blueprint $table) {
            $table->index('created_at');                                       // Time Stamps
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('gateway_calls', function (Blueprint $table) {
            $table->dropIndex('created_at');                                       // Time Stamps
        });
    }
}
