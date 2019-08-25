<?php

use tests\TestProject\Domain\Commands\TestCommand;
use Webravo\Application\Service\CommandsQueueService;
use Webravo\Infrastructure\Library\Configuration;
// use Faker\Factory;
use tests\TestProject\Domain\AggregateRoot\TestTransaction;
use tests\TestProject\Domain\Events\TestTransactionAddedEvent;
use tests\TestProject\Domain\Commands\TestTransactionCreateCommand;
use tests\TestProject\Domain\Commands\TestTransactionSetStatusCommand;


class EventSourceTest extends TestCase
{
    public function testEventSourceOne()
    {
        $googleConfigFile = Configuration::get('GOOGLE_APPLICATION_CREDENTIALS');
        self::assertTrue(file_exists($googleConfigFile), "Google Credential file $googleConfigFile does not exists");

        $service = new CommandsQueueService([
            'command_queue_service' => 'sync',
            'command_store_service' => 'discard',
        ]);

        $command = new TestTransactionCreateCommand("abcd");

        $command_response = $service->dispatchCommand($command);

        // $event = new TestTransactionAddedEvent("abcd");
        // $t = TestTransaction::newTransaction();
        // $t->recordAndApplyThat($event);
        // $stream = $t->getEventStream();

        $stream = $command_response->allEvents();

        $t2 = TestTransaction::rebuildFromHistory($stream);

        $transaction_id = $t2->getAggregateId();

        $command2 = new TestTransactionSetStatusCommand($transaction_id, "PENDING");

        $command_response = $service->dispatchCommand($command2);

    }
}