<?php

use Faker\Factory;

use tests\TestProject\Domain\Commands\TestCommand;
use Webravo\Application\Service\CommandsQueueService;
use Webravo\Application\Service\EventsQueueService;
use Webravo\Infrastructure\Library\Configuration;
use tests\TestProject\Domain\AggregateRoot\TestTransaction;
use tests\TestProject\Domain\Events\TestTransactionAddedEvent;
use tests\TestProject\Domain\Commands\TestTransactionCreateCommand;
use tests\TestProject\Domain\Commands\TestTransactionSetStatusCommand;
use tests\TestProject\Domain\Repository\TestRepository;
use tests\TestProject\Domain\Projection\TestTransactionByStatusProjection;
use tests\TestProject\Persistence\Memory\TestTransactionMemoryStore;
use tests\TestProject\Domain\Repository\TestTransactionRepository;

class EventSourceTest extends TestCase
{
    public function testEventSourceOne()
    {
        $googleConfigFile = Configuration::get('GOOGLE_APPLICATION_CREDENTIALS');
        self::assertTrue(file_exists($googleConfigFile), "Google Credential file $googleConfigFile does not exists");

        app()->bind('tests\TestProject\Domain\Repository\TestTransactionEventStreamRepositoryInterface', 'tests\TestProject\Domain\Repository\TestTransactionEventStreamRepository');
        app()->bind('tests\TestProject\Infrastructure\Repository\TestTransactionEventStreamStoreInterface', 'tests\TestProject\Persistence\Memory\TestTransactionEventStoreMemoryStore');

        $faker = Factory::create();

        // Testing TransactionMemoryStore
        $store = new TestTransactionMemoryStore();
        $t = TestTransaction::newTransaction();
        // $fk = $faker->numberBetween(1000,100000);
        $t->setTransactionKey($faker->bankAccountNumber);
        $t->setStatus('DENIED');
        $guid = $t->getAggregateId();
        $store->persistEntity($t);
        $a_transaction = $store->getByGuid($guid);
        $t2 = TestTransaction::newTransaction();
        $t2->fromArray($a_transaction);

        self::assertEquals($t->getAggregateId(), $t2->getAggregateId(), "Store error: cannot retrieve transaction by id");
        $store->deleteByGuid($guid);
        self::assertNull($store->getByGuid($guid), "Store delete error: object with guid $guid is not deleted");

        $store2 = new TestTransactionMemoryStore();


        $command_queue = new CommandsQueueService([
            'command_queue_service' => 'sync',
            'command_store_service' => 'discard',
        ]);

        $event_queue = new EventsQueueService([
            'event_queue_service' => 'sync',
            'event_store_service' => 'discard'
        ]);

        $repository = new TestTransactionRepository($store);
        $repository2 = new TestTransactionRepository($store2);
        $projection = new TestTransactionByStatusProjection($repository);
        $projection2 = new TestTransactionByStatusProjection($repository2);

        $projection->testValue = 1;
        $projection2->testValue = 2;

        $projection->subscribe($event_queue);
        $projection2->subscribe($event_queue);


        $tkey = $faker->bankAccountNumber;
        $command = new TestTransactionCreateCommand($tkey);

        $command_response = $command_queue->dispatchCommand($command);

        // $event = new TestTransactionAddedEvent("abcd");
        // $t = TestTransaction::newTransaction();
        // $t->recordAndApplyThat($event);
        // $stream = $t->getEventStream();

        $stream = $command_response->allEvents();

        foreach($stream as $event) {
            $event_queue->dispatchEvent($event);
        }

        $t2 = TestTransaction::rebuildFromHistory($stream);

        self::assertEquals($tkey, $t2->getTransactionKey(), "RebuildFromHistory error (1)");

        $transaction_id = $t2->getAggregateId();

        $command2 = new TestTransactionSetStatusCommand($transaction_id, "PENDING");

        $command_response = $command_queue->dispatchCommand($command2);
        $stream = $command_response->allEvents();

        foreach($stream as $event) {
            $event_queue->dispatchEvent($event);
        }

        $t3 = TestTransaction::rebuildFromHistory($stream);

        self::assertEquals($tkey, $t3->getTransactionKey(), "RebuildFromHistory error (2)");
        self::assertEquals("PENDING", $t3->getStatus(), "RebuildFromHistory error (3)");


    }
}