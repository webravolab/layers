<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTestEntityTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::connection('testbench')->hasTable('test_entity')) {
            Schema::connection('testbench')->create('test_entity', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('guid', 100)->index();
                $table->timestampTz('created_at')->nullable();
                $table->string('name', 255)->nullable();
                $table->bigInteger('fk_id')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::connection('testbench')->hasTable('test_entity')) {
            Schema::connection('testbench')->dropIfExists('test_entity');
        }
    }
}
