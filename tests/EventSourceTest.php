<?php

use tests\TestProject\Domain\Commands\TestCommand;
use Webravo\Application\Service\CommandsQueueService;
use Webravo\Application\Service\EventsQueueService;
use Webravo\Infrastructure\Library\Configuration;
// use Faker\Factory;
use tests\TestProject\Domain\AggregateRoot\TestTransaction;
use tests\TestProject\Domain\Events\TestTransactionAddedEvent;
use tests\TestProject\Domain\Commands\TestTransactionCreateCommand;
use tests\TestProject\Domain\Commands\TestTransactionSetStatusCommand;
use tests\TestProject\Domain\Repository\TestRepository;
use tests\TestProject\Domain\Projection\TestTransactionByStatusProjection;
use tests\TestProject\Persistence\Memory\Store\TestTransactionMemoryStore;

class EventSourceTest extends TestCase
{
    public function testEventSourceOne()
    {
        $googleConfigFile = Configuration::get('GOOGLE_APPLICATION_CREDENTIALS');
        self::assertTrue(file_exists($googleConfigFile), "Google Credential file $googleConfigFile does not exists");

        // Testing TransactionMemoryStore
        $store = new TestTransactionMemoryStore();
        $t = TestTransaction::newTransaction();
        $t->setTransactionKey('123');
        $t->setStatus('DENIED');
        $guid = $t->getAggregateId();
        $store->persistEntity($t);
        $a_transaction = $store->getByGuid($guid);
        $t2 = TestTransaction::newTransaction();
        $t2->fromArray($a_transaction);
        $store->deleteByGuid($guid);



        $command_queue = new CommandsQueueService([
            'command_queue_service' => 'sync',
            'command_store_service' => 'discard',
        ]);

        $event_queue = new \Webravo\Application\Service\EventsQueueService([
            'event_queue_service' => 'sync',
            'event_store_service' => 'discard'
        ]);

        $repository = new TestRepository();
        $projection = new TestTransactionByStatusProjection($repository);

        $command = new TestTransactionCreateCommand("abcd");

        $command_response = $command_queue->dispatchCommand($command);

        // $event = new TestTransactionAddedEvent("abcd");
        // $t = TestTransaction::newTransaction();
        // $t->recordAndApplyThat($event);
        // $stream = $t->getEventStream();

        $stream = $command_response->allEvents();

        $t2 = TestTransaction::rebuildFromHistory($stream);

        $transaction_id = $t2->getAggregateId();

        $command2 = new TestTransactionSetStatusCommand($transaction_id, "PENDING");

        $command_response = $command_queue->dispatchCommand($command2);

    }
}