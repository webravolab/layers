<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJobsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::connection('testbench')->hasTable('jobs')) {
            Schema::connection('testbench')->create('jobs', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('guid', 100)->index();
                $table->string('name', 100)->nullable();
                $table->string('channel', 100)->index();
                $table->string('routing_key', 100)->nullable();
                $table->enum('status', ['QUEUED', 'DELIVERED', 'FAILED', 'ACK', 'NACK'])->default('QUEUED');
                $table->timestamp('created_at')->nullable();
                $table->timestamp('delivered_at')->nullable();
                $table->string('delivered_token', 100)->nullable()->index();
                $table->longText('payload')->nullable();
                $table->longText('header')->nullable();
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
        if (!Schema::connection('testbench')->hasTable('jobs')) {
            Schema::connection('testbench')->dropIfExists('jobs');
        }
    }
}
