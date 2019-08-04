<?php

use Faker\Factory;
use Webravo\Infrastructure\Library\Configuration;
use Webravo\Persistence\Eloquent\Store\EloquentEventStore;
use Webravo\Application\Event\EventBusDispatcher;
use Webravo\Application\Event\EventBucketBusMiddleware;
use Webravo\Persistence\Datastore\Store\DataStoreEventStore;
use Webravo\Persistence\BigQuery\Store\BigQueryEventStore;
use tests\TestProject\Domain\Events\TestEvent;

class EventStoreTest extends TestCase
{

    public function testEloquentEventStore()
    {
        $eventStore = new EloquentEventStore();

        $event = new TestEvent();
        $event->setStrValue('this is a string');
        $event->setIntValue((int) Rand(1,9999));
        $event->setFloatValue((float) Rand());

        $payload = [
            'value' => 'this is a test value ' . str_repeat('x', 1500),
            'number' => 175,
            'float' => 1.75,
        ];
        $event->setPayload($payload);

        $guid = $event->getGuid();

        $eventStore->append($event);

        $retrieved_event = $eventStore->getByGuid($guid);

        $this->assertEquals($event->getPayload(), $retrieved_event->getPayload());
        $this->assertEquals($event->getIntValue(), $retrieved_event->getIntValue());
        $this->assertEquals($event->getFloatValue(), $retrieved_event->getFloatValue());
        $this->assertEquals($event->getStrValue(), $retrieved_event->getStrValue());
        $this->assertEquals($event->getOccurredAt()->format(DATE_RFC3339_EXTENDED), $retrieved_event->getOccurredAt()->format(DATE_RFC3339_EXTENDED));
    }

    public function testDataStoreEventStore()
    {
        $googleConfigFile = Configuration::get('GOOGLE_APPLICATION_CREDENTIALS');
        self::assertTrue(file_exists($googleConfigFile), "Google Credential file $googleConfigFile does not exists");

        $eventStore = new DataStoreEventStore();

        $event = new TestEvent();
        $event->setStrValue('this is a string');
        $event->setIntValue((int) Rand(1,9999));
        $event->setFloatValue((float) Rand());
        $payload = [
            'value' => 'this is a test value ' . str_repeat('x', 1500),
            'number' => 175,
            'float' => 1.75,
        ];
        $event->setPayload($payload);

        $guid = $event->getGuid();
        $class_name = $event->getClassName();

        $eventStore->append($event);

        $retrieved_event = $eventStore->getByGuid($guid, $event->getType());

        $this->assertEquals($event->getPayload(), $retrieved_event->getPayload());
        $this->assertEquals($event->getIntValue(), $retrieved_event->getIntValue());
        $this->assertEquals($event->getFloatValue(), $retrieved_event->getFloatValue());
        $this->assertEquals($event->getStrValue(), $retrieved_event->getStrValue());
        $this->assertEquals($event->getOccurredAt()->format(DATE_RFC3339_EXTENDED), $retrieved_event->getOccurredAt()->format(DATE_RFC3339_EXTENDED));
    }

    public function testBigQueryEventStore()
    {
        $googleConfigFile = Configuration::get('GOOGLE_APPLICATION_CREDENTIALS');
        self::assertTrue(file_exists($googleConfigFile), "Google Credential file $googleConfigFile does not exists");

        $faker = Factory::create();

        $eventStore = new BigQueryEventStore();

        $event = new TestEvent();
        $event->setStrValue($faker->name());
        $event->setIntValue($faker->numberBetween(1,100000));
        $event->setFloatValue((float) Rand());
        $payload = [
            'number' => $faker->numberBetween(1,999),
            'float' => 1.75,
            'fk_id' => $faker->numberBetween(1,10000000),
            'value' => $faker->sentence()  . str_repeat('x', 1500),
        ];
        $event->setPayload($payload);

        $guid = $event->getGuid();
        $class_name = $event->getClassName();

        $eventStore->append($event);

        $retrieved_event = $eventStore->getByGuid($guid, $event->getType());

        $this->assertEquals($event->getPayload(), $retrieved_event->getPayload());
        $this->assertEquals($event->getIntValue(), $retrieved_event->getIntValue());
        $this->assertEquals($event->getFloatValue(), $retrieved_event->getFloatValue());
        $this->assertEquals($event->getStrValue(), $retrieved_event->getStrValue());
        $this->assertEquals($event->getOccurredAt()->format(DATE_RFC3339_EXTENDED), $retrieved_event->getOccurredAt()->format(DATE_RFC3339_EXTENDED));
    }

    public function testDBStoreEventBus()
    {
        $eventStore = new EloquentEventStore();
        $eventLocalDispatcher = new EventBusDispatcher();

        // Instantiate an Event Store using DB as underlying storage
        $eventBus = new EventBucketBusMiddleware($eventLocalDispatcher, $eventStore);

        $event = new TestEvent();
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

        $event = new TestEvent();

        $payload = [
            'value' => 'this is a test value ' . str_repeat('x', 1500),
            'number' => 175,
            'float' => 1.75,
        ];

        $event->setPayload( $payload);

        $guid = $event->getGuid();

        $eventBus->dispatch($event);

        $retrieved_event = $eventStore->getByGuid($guid);

        $this->assertEquals($event->getPayload(), $retrieved_event->getPayload());
        $this->assertEquals($event->getStrValue(), $retrieved_event->getStrValue());
        $this->assertEquals($event->getIntValue(), $retrieved_event->getIntValue());
        $this->assertEquals($event->getFloatValue(), $retrieved_event->getFloatValue());
        $this->assertEquals($event->getOccurredAt()->format(DATE_RFC3339_EXTENDED), $retrieved_event->getOccurredAt()->format(DATE_RFC3339_EXTENDED));

    }

    public function testBigQueryEventBus()
    {
        $eventStore = new BigQueryEventStore();
        $eventLocalDispatcher = new EventBusDispatcher();

        // Instantiate an Event Store using Google Data Store as underlying storage
        $eventBus = new EventBucketBusMiddleware($eventLocalDispatcher, $eventStore);

        $event = new TestEvent();

        $payload = [
            'value' => 'this is a bus test value ' . str_repeat('x', 1500),
            'number' => 444,
            'float' => 4.75,
        ];

        $event->setPayload( $payload);

        $guid = $event->getGuid();

        $eventBus->dispatch($event);

        $retrieved_event = $eventStore->getByGuid($guid);

        $this->assertEquals($event->getPayload(), $retrieved_event->getPayload());
        $this->assertEquals($event->getStrValue(), $retrieved_event->getStrValue());
        $this->assertEquals($event->getIntValue(), $retrieved_event->getIntValue());
        $this->assertEquals($event->getFloatValue(), $retrieved_event->getFloatValue());
        $this->assertEquals($event->getOccurredAt()->format(DATE_RFC3339_EXTENDED), $retrieved_event->getOccurredAt()->format(DATE_RFC3339_EXTENDED));

    }

}
