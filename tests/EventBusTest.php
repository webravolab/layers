<?php

use Webravo\Infrastructure\Library\Configuration;
use Webravo\Persistence\Eloquent\Store\EloquentEventStore;
use Webravo\Application\Event\EventBusDispatcher;
use Webravo\Application\Event\EventStoreBusMiddleware;
use Webravo\Persistence\Datastore\Store\DataStoreEventStore;
use Webravo\Persistence\Service\RabbitMQService;

class EventBusTest extends TestCase
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
        $eventBus = new EventStoreBusMiddleware($eventLocalDispatcher, $eventStore);

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
        $eventBus = new EventStoreBusMiddleware($eventLocalDispatcher, $eventStore);

        $event = new \tests\Events\TestEvent();

        $payload = new stdClass();
        $payload->value = 'this is a test value';
        $payload->number = 175;
        $payload->float = 1.75;

        $event->setPayload( $payload);

        $guid = $event->getGuid();

        $eventBus->dispatch($event);

        $retrieved_event = $eventStore->getByGuid($guid);

        $this->assertEquals($event->getPayload(), $retrieved_event->getPayload());

        $this->assertEquals($event->getOccurredAt(), $retrieved_event->getOccurredAt());

    }


    public function testRabbitMQBus() {


        $publisherService = new RabbitMQService();
        $publisherService->createChannel('fanout', 'fanout-bind-exchange');

        $subscriberService1 = new RabbitMQService();
        $subscriberService1->createChannel('fanout', 'fanout-bind-exchange');
        $subscriberService1->createQueue('test-event-bus');
        $subscriberService1->subscribeQueue('test-event-bus', 'fanout-bind-exchange');

        $event = new \tests\Events\TestEvent();

        $payload = new stdClass();
        $payload->value = 'this is a test value';
        $payload->number = 175;
        $payload->float = 1.75;

        $serializedPayload = json_encode($payload);
        $publisherService->publishMessage($serializedPayload, '');

        $message1 = $subscriberService1->getSingleMessage('test-event-bus');

        $this->assertNotNull($message1, 'Message 11 must not be null');

        if ($message1) {
            echo "(bind) Message 1 received: " . $message1->body . "\n";
            $subscriberService1->messageAcknowledge($message1);
        }

        $subscriberService1->unsubscribeQueue('test-event-bind1', 'fanout-bind-exchange');
        $publisherService->close();
        $subscriberService1->close();
    }
}
