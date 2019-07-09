<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCommandsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::connection('testbench')->hasTable('commands')) {
            Schema::connection('testbench')->create('commands', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('guid', 100)->index();
                $table->string('command', 100)->nullable();
                $table->string('queue_name', 100)->nullable();
                $table->string('binding_key', 100)->nullable();
                $table->longText('payload')->nullable();
                $table->longText('header')->nullable();
                $table->timestamp('created_at')->nullable();
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
        if (!Schema::connection('testbench')->hasTable('commands')) {
            Schema::connection('testbench')->dropIfExists('commands');
        }
    }
}
