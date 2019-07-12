<?php

use Webravo\Infrastructure\Library\Configuration;
use Webravo\Persistence\Eloquent\Store\EloquentEventStore;
use Webravo\Application\Event\EventBusDispatcher;
use Webravo\Application\Event\EventBucketBusMiddleware;
use Webravo\Persistence\Datastore\Store\DataStoreEventStore;

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


        // $guid = "194db1c0-a4a8-11e9-a106-45ff99f6becd";
        // $retrieved_event = $eventStore->getByGuid($guid, 'TestEvent');



        $event = new \tests\Events\TestEvent();
        $event->setPayload([
            'payload1' => 'payload value 1',
            'payload2' => 'payload value 2',
        ]);
        $event->setStrValue('this is a string');
        $event->setIntValue((int) Rand(1,9999));
        $event->setFloatValue((float) Rand());

        $guid = $event->getGuid();
        $class_name = $event->getClassName();

        $eventStore->Append($event);

        $retrieved_event = $eventStore->getByGuid($guid, $event->getType());

        $this->assertEquals($event->getIntValue(), $retrieved_event->getIntValue());
        $this->assertEquals($event->getFloatValue(), $retrieved_event->getFloatValue());
        $this->assertEquals($event->getStrValue(), $retrieved_event->getStrValue());
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
