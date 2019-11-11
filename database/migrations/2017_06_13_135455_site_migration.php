<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SiteMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Create Site Migration Table
        Schema::create('site_migration', function (Blueprint $table) {
            $table->increments('id');
            $table->text('sitecode');
            $table->text('comment')->nullable();            // Comment
            $table->text('trunking')->nullable();           // Comment
            $table->text('e911')->nullable();               // Comment
            $table->text('srstip')->nullable();             // Comment
            $table->json('h323ip')->nullable();
            $table->text('npa')->nullable();               // Comment
            $table->json('backups')->nullable();           // JSON Details Custom Field Data
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
        Schema::drop('site_migration');
    }
}
