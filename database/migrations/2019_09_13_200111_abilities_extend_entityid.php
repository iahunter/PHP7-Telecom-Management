<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AbilitiesExtendEntityid extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */

    /* Fix for error:
        [Doctrine\DBAL\DBALException]
        Unknown database type json requested, Doctrine\DBAL\Platforms\MySqlPlatform may not support it.
        https://stackoverflow.com/questions/48256476/unknown-database-type-json-requested-doctrine-dbal-platforms-mysql57platform-m
    */
    public function __construct()
    {
        DB::getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('json', 'string');
    }

    /* This migration changes entity_id from integer to string for using CUCM UUID as entity_id. */

    public function up()
    {
        //
        Schema::table('abilities', function ($table) {
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
        Schema::table('abilities', function ($table) {
            $table->integer('entity_id')->unsigned()->nullable()->change();
        });
    }
}
