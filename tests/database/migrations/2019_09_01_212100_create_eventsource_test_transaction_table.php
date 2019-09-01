<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEventsourceTestTransactionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::connection('testbench')->hasTable('eventsource_test_transaction')) {
            Schema::connection('testbench')->create('eventsource_test_transaction', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('guid', 100)->index();
                $table->string('aggregate_type', 100);
                $table->string('aggregate_id', 100);
                $table->string('event', 100);
                $table->integer('version');
                $table->timestamp('occurred_at');
                $table->longText('payload')->nullable();
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
        if (Schema::connection('testbench')->hasTable('eventsource_test_transaction')) {
            Schema::connection('testbench')->dropIfExists('eventsource_test_transaction');
        }
    }
}
