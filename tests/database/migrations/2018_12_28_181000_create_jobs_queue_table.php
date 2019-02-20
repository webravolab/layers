<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJobsQueueTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::connection('testbench')->hasTable('jobs_queue')) {
            Schema::connection('testbench')->create('jobs_queue', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('guid', 100)->index();
                $table->string('queue_name', 100)->index();
                $table->string('channel', 100)->index();
                $table->enum('strategy', ['', 'fanout', 'direct', 'topic'])->default('');
                $table->string('routing_key', 100)->nullable();
                $table->enum('status', ['ACTIVE', 'DELETED', 'BIND', 'UNBIND'])->default('ACTIVE');
                $table->timestamp('created_at')->nullable();
                $table->timestamp('last_run_at')->nullable();
                $table->bigInteger('messages_total')->default(0);
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
        if (Schema::connection('testbench')->hasTable('jobs_queue')) {
            Schema::connection('testbench')->dropIfExists('jobs_queue');
        }
    }
}
