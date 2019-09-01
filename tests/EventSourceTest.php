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
use Webravo\Infrastructure\Library\DependencyBuilder;
use Project\Domain\Service\TestTransactionService;
use Webravo\Persistence\Datastore\Store\DataStoreEventStreamStore;
use Webravo\Persistence\Service\DataStoreService;
use Webravo\Persistence\Eloquent\Store\EloquentEventStreamStore;

class EventSourceTest extends TestCase
{


    public function testEventSourceEloquent()
    {
        $googleConfigFile = Configuration::get('GOOGLE_APPLICATION_CREDENTIALS');
        self::assertTrue(file_exists($googleConfigFile), "Google Credential file $googleConfigFile does not exists");

        // $dataStoreClient = new DataStoreService();
        // $event_store = new DataStoreEventStreamStore();

        app()->bind('tests\TestProject\Domain\Repository\TestTransactionEventStreamRepositoryInterface', 'tests\TestProject\Domain\Repository\TestTransactionEventStreamRepository');
        app()->bind('tests\TestProject\Infrastructure\Repository\TestTransactionEventStreamStoreInterface', 'tests\TestProject\Persistence\Memory\TestTransactionEventStoreMemoryStore');

        $faker = Factory::create();

        $command_queue = new CommandsQueueService([
            'command_queue_service' => 'sync',
            'command_store_service' => 'discard',
        ]);

        $event_queue = new EventsQueueService([
            'event_queue_service' => 'sync',
            'event_store_service' => 'discard'
        ]);

        // $event_store = new \tests\TestProject\Persistence\Memory\TestTransactionEventStoreMemoryStore();
        $event_repository = new EloquentEventStreamStore();

        // Instantiate a TransactionService
        $service = new TestTransactionService($command_queue, $event_repository);

        $store = new TestTransactionMemoryStore();
        $store2 = new TestTransactionMemoryStore();

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

        $changed_stream = $command_response->allEvents();

        foreach($changed_stream as $event) {
            $event_queue->dispatchEvent($event);
        }

        $transaction_id = $changed_stream->getAggregateId();

        $t2 = $service->getTestTransactionById($transaction_id);

        self::assertEquals($tkey, $t2->getTransactionKey(), "RebuildFromHistory error (1)");

        $version = $t2->getVersion();

        $command = new TestTransactionSetStatusCommand($transaction_id, "STATUS 2");
        $command_response = $command_queue->dispatchCommand($command);
        $changed_stream = $command_response->allEvents();
        // Get only the new events generated by the last command
        // $new_stream = $stream->allEventsSinceVersion($version);
        foreach($changed_stream as $event) {
            $event_queue->dispatchEvent($event);
        }

        $command = new TestTransactionSetStatusCommand($transaction_id, "STATUS 3");
        $command_response = $command_queue->dispatchCommand($command);
        $changed_stream = $command_response->allEvents();
        foreach($changed_stream as $event) {
            $event_queue->dispatchEvent($event);
        }

        $command = new TestTransactionSetStatusCommand($transaction_id, "STATUS 4");
        $command_response = $command_queue->dispatchCommand($command);
        $changed_stream = $command_response->allEvents();
        foreach($changed_stream as $event) {
            $event_queue->dispatchEvent($event);
        }

        $command = new TestTransactionSetStatusCommand($transaction_id, "STATUS 5");
        $command_response = $command_queue->dispatchCommand($command);
        $changed_stream = $command_response->allEvents();
        foreach($changed_stream as $event) {
            $event_queue->dispatchEvent($event);
        }

        $command = new TestTransactionSetStatusCommand($transaction_id, "STATUS 6");
        $command_response = $command_queue->dispatchCommand($command);
        $changed_stream = $command_response->allEvents();
        foreach($changed_stream as $event) {
            $event_queue->dispatchEvent($event);
        }

        $command = new TestTransactionSetStatusCommand($transaction_id, "DENIED");
        $command_response = $command_queue->dispatchCommand($command);
        $changed_stream = $command_response->allEvents();
        foreach($changed_stream as $event) {
            $event_queue->dispatchEvent($event);
        }

        $t3 = $service->getTestTransactionById($transaction_id);

        self::assertEquals($tkey, $t3->getTransactionKey(), "RebuildFromHistory error (2)");
        self::assertEquals("DENIED", $t3->getStatus(), "RebuildFromHistory error (3)");


    }

    public function testEventSourceMemory()
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
        $t->setStatus('PENDING');
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

        $event_store = new \tests\TestProject\Persistence\Memory\TestTransactionEventStoreMemoryStore();
        $event_repository = new \tests\TestProject\Domain\Repository\TestTransactionEventStreamRepository($event_store);

        // Instantiate a TransactionService
        $service = new TestTransactionService($command_queue, $event_repository);

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

        $changed_stream = $command_response->allEvents();

        foreach($changed_stream as $event) {
            $event_queue->dispatchEvent($event);
        }

        $transaction_id = $changed_stream->getAggregateId();

        $t2 = $service->getTestTransactionById($transaction_id);

        self::assertEquals($tkey, $t2->getTransactionKey(), "RebuildFromHistory error (1)");

        $version = $t2->getVersion();

        $command = new TestTransactionSetStatusCommand($transaction_id, "STATUS 2");
        $command_response = $command_queue->dispatchCommand($command);
        $changed_stream = $command_response->allEvents();
        // Get only the new events generated by the last command
        // $new_stream = $stream->allEventsSinceVersion($version);
        foreach($changed_stream as $event) {
            $event_queue->dispatchEvent($event);
        }

        $command = new TestTransactionSetStatusCommand($transaction_id, "STATUS 3");
        $command_response = $command_queue->dispatchCommand($command);
        $changed_stream = $command_response->allEvents();
        foreach($changed_stream as $event) {
            $event_queue->dispatchEvent($event);
        }

        $command = new TestTransactionSetStatusCommand($transaction_id, "STATUS 4");
        $command_response = $command_queue->dispatchCommand($command);
        $changed_stream = $command_response->allEvents();
        foreach($changed_stream as $event) {
            $event_queue->dispatchEvent($event);
        }

        $command = new TestTransactionSetStatusCommand($transaction_id, "STATUS 5");
        $command_response = $command_queue->dispatchCommand($command);
        $changed_stream = $command_response->allEvents();
        foreach($changed_stream as $event) {
            $event_queue->dispatchEvent($event);
        }

        $command = new TestTransactionSetStatusCommand($transaction_id, "STATUS 6");
        $command_response = $command_queue->dispatchCommand($command);
        $changed_stream = $command_response->allEvents();
        foreach($changed_stream as $event) {
            $event_queue->dispatchEvent($event);
        }

        $command = new TestTransactionSetStatusCommand($transaction_id, "DENIED");
        $command_response = $command_queue->dispatchCommand($command);
        $changed_stream = $command_response->allEvents();
        foreach($changed_stream as $event) {
            $event_queue->dispatchEvent($event);
        }

        $t3 = $service->getTestTransactionById($transaction_id);

        self::assertEquals($tkey, $t3->getTransactionKey(), "RebuildFromHistory error (2)");
        self::assertEquals("DENIED", $t3->getStatus(), "RebuildFromHistory error (3)");

    }

    public function testEventSourceDatastore()
    {
        $googleConfigFile = Configuration::get('GOOGLE_APPLICATION_CREDENTIALS');
        self::assertTrue(file_exists($googleConfigFile), "Google Credential file $googleConfigFile does not exists");

        // $dataStoreClient = new DataStoreService();
        // $event_store = new DataStoreEventStreamStore();

        app()->bind('tests\TestProject\Domain\Repository\TestTransactionEventStreamRepositoryInterface', 'tests\TestProject\Domain\Repository\TestTransactionEventStreamRepository');
        app()->bind('tests\TestProject\Infrastructure\Repository\TestTransactionEventStreamStoreInterface', 'tests\TestProject\Persistence\Memory\TestTransactionEventStoreMemoryStore');

        $faker = Factory::create();

        $command_queue = new CommandsQueueService([
            'command_queue_service' => 'sync',
            'command_store_service' => 'discard',
        ]);

        $event_queue = new EventsQueueService([
            'event_queue_service' => 'sync',
            'event_store_service' => 'discard'
        ]);

        // $event_store = new \tests\TestProject\Persistence\Memory\TestTransactionEventStoreMemoryStore();
        $event_repository = new DataStoreEventStreamStore();

        // Instantiate a TransactionService
        $service = new TestTransactionService($command_queue, $event_repository);

        $store = new TestTransactionMemoryStore();
        $store2 = new TestTransactionMemoryStore();

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

        $changed_stream = $command_response->allEvents();

        foreach($changed_stream as $event) {
            $event_queue->dispatchEvent($event);
        }

        $transaction_id = $changed_stream->getAggregateId();

        $t2 = $service->getTestTransactionById($transaction_id);

        self::assertEquals($tkey, $t2->getTransactionKey(), "RebuildFromHistory error (1)");

        $version = $t2->getVersion();

        $command = new TestTransactionSetStatusCommand($transaction_id, "STATUS 2");
        $command_response = $command_queue->dispatchCommand($command);
        $changed_stream = $command_response->allEvents();
        // Get only the new events generated by the last command
        // $new_stream = $stream->allEventsSinceVersion($version);
        foreach($changed_stream as $event) {
            $event_queue->dispatchEvent($event);
        }

        $command = new TestTransactionSetStatusCommand($transaction_id, "STATUS 3");
        $command_response = $command_queue->dispatchCommand($command);
        $changed_stream = $command_response->allEvents();
        foreach($changed_stream as $event) {
            $event_queue->dispatchEvent($event);
        }

        $command = new TestTransactionSetStatusCommand($transaction_id, "STATUS 4");
        $command_response = $command_queue->dispatchCommand($command);
        $changed_stream = $command_response->allEvents();
        foreach($changed_stream as $event) {
            $event_queue->dispatchEvent($event);
        }

        $command = new TestTransactionSetStatusCommand($transaction_id, "STATUS 5");
        $command_response = $command_queue->dispatchCommand($command);
        $changed_stream = $command_response->allEvents();
        foreach($changed_stream as $event) {
            $event_queue->dispatchEvent($event);
        }

        $command = new TestTransactionSetStatusCommand($transaction_id, "STATUS 6");
        $command_response = $command_queue->dispatchCommand($command);
        $changed_stream = $command_response->allEvents();
        foreach($changed_stream as $event) {
            $event_queue->dispatchEvent($event);
        }

        $command = new TestTransactionSetStatusCommand($transaction_id, "DENIED");
        $command_response = $command_queue->dispatchCommand($command);
        $changed_stream = $command_response->allEvents();
        foreach($changed_stream as $event) {
            $event_queue->dispatchEvent($event);
        }

        $t3 = $service->getTestTransactionById($transaction_id);

        self::assertEquals($tkey, $t3->getTransactionKey(), "RebuildFromHistory error (2)");
        self::assertEquals("DENIED", $t3->getStatus(), "RebuildFromHistory error (3)");


    }


}