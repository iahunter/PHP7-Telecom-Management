<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPhoneplanToPhoneMacTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('phone_mac', function (Blueprint $table) {
            $table->integer('phoneplan_id')->unsigned()->index()->nullable();     // Phone Plan that this MAC belongs to if in planning
            $table->foreign('phoneplan_id')
                    ->references('id')
                    ->on('phoneplan')
                    ->onDelete('cascade');        // Create foreign key and try cascade deletes
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('phone_mac', function (Blueprint $table) {
            $table->dropForeign('phone_mac_phoneplan_id_foreign');
            $table->dropColumn('phoneplan_id');
        });
    }
}
