<?php

use Webravo\Infrastructure\Library\Configuration;
use Webravo\Persistence\Eloquent\Store\EloquentEventStore;
use Webravo\Application\Event\EventBusDispatcher;
use Webravo\Application\Event\EventBucketBusMiddleware;
use Webravo\Persistence\Datastore\Store\DataStoreEventStore;
use Webravo\Persistence\Service\RabbitMQService;

class EventStoreTest extends TestCase
{

    public function testEloquentEventStore()
    {
        $eventStore = new EloquentEventStore();

        $event = new \tests\Events\TestEvent();
        $event->setPayload('test value');
        $guid = $event->getGuid();

        $eventStore->Append($event);

        $retrieved_event = $eventStore->getByGuid($guid);

        $this->assertEquals($event->getPayload(), $retrieved_event->getPayload());
    }

    public function testDataStoreEventStore()
    {
        $googleConfigFile = Configuration::get('GOOGLE_APPLICATION_CREDENTIALS');
        self::assertTrue(file_exists($googleConfigFile), "Google Credential file $googleConfigFile does not exists");

        $eventStore = new DataStoreEventStore();

        $event = new \tests\Events\TestEvent();
        $event->setPayload('test value');
        $guid = $event->getGuid();

        $eventStore->Append($event);

        $retrieved_event = $eventStore->getByGuid($guid);

        $this->assertEquals($event->getPayload(), $retrieved_event->getPayload());
    }

    public function testDBStoreEventBus()
    {
        $eventStore = new EloquentEventStore();
        $eventLocalDispatcher = new EventBusDispatcher();

        // Instantiate an Event Store using DB as underlying storage
        $eventBus = new EventBucketBusMiddleware($eventLocalDispatcher, $eventStore);

        $event = new \tests\Events\TestEvent();
        $event->setPayload('test value');
        $guid = $event->getGuid();

        $eventBus->dispatch($event);

        $retrieved_event = $eventStore->getByGuid($guid);

        $this->assertEquals($event->getPayload(), $retrieved_event->getPayload());

    }

    public function testDataStoreEventBus()
    {
        $eventStore = new DataStoreEventStore();
        $eventLocalDispatcher = new EventBusDispatcher();

        // Instantiate an Event Store using Google Data Store as underlying storage
        $eventBus = new EventBucketBusMiddleware($eventLocalDispatcher, $eventStore);

        $event = new \tests\Events\TestEvent();

        $payload = [
            'value' => 'this is a test value',
            'number' => 175,
            'float' => 1.75,
        ];

        $event->setPayload( $payload);

        $guid = $event->getGuid();

        $eventBus->dispatch($event);

        $retrieved_event = $eventStore->getByGuid($guid);

        $this->assertEquals($event->getPayload(), $retrieved_event->getPayload());

        $this->assertEquals($event->getOccurredAt(), $retrieved_event->getOccurredAt());

    }

}
