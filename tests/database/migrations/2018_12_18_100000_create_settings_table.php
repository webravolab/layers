<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::connection('testbench')->hasTable('settings')) {
            Schema::connection('testbench')->create('settings', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('key', 255)->index();
                $table->longText('value')->nullable();
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
        if (Schema::connection('testbench')->hasTable('settings')) {
            Schema::connection('testbench')->dropIfExists('settings');
        }
    }
}
