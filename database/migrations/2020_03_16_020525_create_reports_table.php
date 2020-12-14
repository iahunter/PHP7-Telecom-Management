<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('parent')->nullable();
            $table->string('category')->nullable();
            $table->string('type')->nullable();
            $table->integer('total')->nullable();
            $table->integer('int0')->nullable();
            $table->integer('int1')->nullable();
            $table->integer('int2')->nullable();
            $table->integer('int3')->nullable();
            $table->integer('int4')->nullable();
            $table->string('stringfield0')->nullable();
            $table->string('stringfield1')->nullable();
            $table->string('stringfield2')->nullable();
            $table->string('stringfield3')->nullable();
            $table->string('stringfield4')->nullable();
            $table->text('custom')->nullable();
            $table->json('stats')->nullable();                      // JSON Custom Field Data
            $table->json('json')->nullable();                       // JSON Custom Field Data
            $table->timestamps();                       			// Time Stamps
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('reports');
    }
}
