<?php

use tests\TestProject\Domain\Events\TestEvent;
use tests\TestProject\Domain\Events\TestEventHandler;
/*
use Tests\Commands\MyTestWithoutHandlerCommand;
use Tests\Events\MyTestEvent;
use Tests\Events\MyTestEventHandler;
*/
use Webravo\Application\Service\EventsQueueService;
use Webravo\Persistence\Service\RabbitMQService;
use Webravo\Persistence\Service\StackDriverLoggerService;

// use Log;
// use App\EventsStore;
// use Mockery;

class EventsQueueServiceTest extends TestCase
{

    public function testQueue2SyncAndStore2DiscardEventQueueService()
    {
        $test = EventsQueueService::instance([
            'event_queue_service' => 'sync',
            'event_store_service' => 'discard'
        ]);

        $service = EventsQueueService::instance([
            'event_queue_service' => 'sync',
            'event_store_service' => 'discard'
        ]);

        $this->assertEquals(serialize($test), serialize($service), "EventsQueueService instance does not work");

        // Mock Logger
        $loggerSpy = Mockery::spy('Psr\Log\LoggerInterface');

        app()->instance('Psr\Log\LoggerInterface', $loggerSpy);

        // Do nothing if handler is already registered
        $service->registerHandler(TestEventHandler::class);

        // Mock Command Handler
        $handler = Mockery::spy(TestEventHandler::class);

        app()->instance('tests\TestProject\Domain\Events\TestEventHandler', $handler);

        $event = new TestEvent();
        $event->setPayload([ 'value' => 'test-' . date('H-i-s-u')]);

        $service->dispatchEvent($event);

        // Do nothing as no queue is not configured
        $service->processEventsQueue();

        $handler->shouldHaveReceived('handle')->withArgs([$event]);

        // $loggerSpy->shouldHaveReceived('debug')->withArgs(['Fire event: ' . $event->getName()]);
    }

    public function testQueue2SyncAndStore2DatastoreEventQueueService()
    {
        $service = EventsQueueService::instance([
            'event_queue_service' => 'sync',
            'event_store_service' => 'datastore'
        ]);

        // Mock Logger
        $loggerSpy = Mockery::spy('Psr\Log\LoggerInterface');

        app()->instance('Psr\Log\LoggerInterface', $loggerSpy);

        // Do nothing if handler is already registered
        $service->registerHandler(TestEventHandler::class);

        // Mock Command Handler
        $handler = Mockery::spy(TestEventHandler::class);

        app()->instance('tests\TestProject\Domain\Events\TestEventHandler', $handler);

        $event = new TestEvent();
        $event->setPayload([ 'value' => 'test-' . date('H-i-s-u')]);

        $service->dispatchEvent($event);

        // Do nothing as no queue is not configured
        $service->processEventsQueue();

        $handler->shouldHaveReceived('handle')->withArgs([$event]);

        // $loggerSpy->shouldHaveReceived('debug')->withArgs(['Fire event: ' . $event->getName()]);
    }

    public function testQueue2SyncAndStore2BigQueryEventQueueService()
    {
        $service = EventsQueueService::instance([
            'event_queue_service' => 'sync',
            'event_store_service' => 'bigquery'
        ]);

        // Mock Logger
        $loggerSpy = Mockery::spy('Psr\Log\LoggerInterface');

        app()->instance('Psr\Log\LoggerInterface', $loggerSpy);

        // Do nothing if handler is already registered
        $service->registerHandler(TestEventHandler::class);

        // Mock Command Handler
        $handler = Mockery::spy(TestEventHandler::class);

        app()->instance('tests\TestProject\Domain\Events\TestEventHandler', $handler);

        $event = new TestEvent();
        $event->setPayload([ 'value' => 'test-' . date('H-i-s-u')]);

        $service->dispatchEvent($event);

        // Do nothing as no queue is not configured
        $service->processEventsQueue();

        $handler->shouldHaveReceived('handle')->withArgs([$event]);

        // $loggerSpy->shouldHaveReceived('debug')->withArgs(['Fire event: ' . $event->getName()]);
    }

    public function testSimulateRemoteEventDispatch()
    {
        $service = EventsQueueService::instance([
            'event_queue_service' => 'rabbitmq',
            'event_store_service' => 'discard',
            'event_queue' => 'test-event-bus02'
        ]);

        $event = new TestEvent();
        $event->setPayload([ 'value' => 'test-' . date('H-i-s-u')]);
        $event->setStrValue( 'test-' . date('H-i-s-u'));

        $result = $service->dispatchEvent($event);

        // Mock Command Handler
        $handler = Mockery::spy(TestHandler::class);
        app()->instance('tests\TestProject\Domain\Events\TestEventHandler', $handler);

        // $handler->shouldReceive('handle')->withAnyArgs();

        // Create a second instance of QueueService to simulate the event receiver process
        $service2 = new EventsQueueService([
            'event_queue_service' => 'rabbitmq',
            'event_store_service' => 'discard',
            'event_queue' => 'test-event-bus02'
        ]);

        // Need to register handler manually because cannot inject domain-events config
        $service2->registerHandler(TestEventHandler::class);

        $service2->processEventsQueue();

        $handler->shouldHaveReceived('handle');

    }

    public function testTopicEventDispatch()
    {

        $publisherService = EventsQueueService::instance([
            'event_queue_service' => 'rabbitmq',
            'event_exchange_name' => 'test-topic-exchange',
            'event_queue' => 'test-topic-queue',
            'event_exchange_mode' => 'topic'
        ]);

        $receiverService1 = EventsQueueService::instance([
            'event_queue_service' => 'rabbitmq',
            'event_exchange_name' => 'test-topic-exchange',
            'event_queue' => 'test-topic-front',
            'event_exchange_mode' => 'topic',
            'event_topic' => 'front.*'
        ]);

        $receiverService2 = EventsQueueService::instance([
            'event_queue_service' => 'rabbitmq',
            'event_exchange_name' => 'test-topic-exchange',
            'event_queue' => 'test-topic-back',
            'event_exchange_mode' => 'topic',
            'event_topic' => 'back.*'
        ]);

        $event = new TestEvent();
        $event->setPayload([ 'value' => 'test-ok-' . date('H-i-s-u')]);
        $event->setStrValue( 'test-ok-' . date('H-i-s-u'));

        $result = $publisherService->dispatchEvent($event, 'front.analytics');

        $event2 = new TestEvent();
        $event2->setPayload([ 'value' => 'test-bad-' . date('H-i-s-u')]);
        $event2->setStrValue( 'test-bad' . date('H-i-s-u'));

        $result = $publisherService->dispatchEvent($event2, 'back.ajax');

        // Mock Command Handler
        $handler = Mockery::spy(TestHandler::class);
        app()->instance('tests\TestProject\Domain\Events\TestEventHandler', $handler);

        // $handler->shouldReceive('handle')->withAnyArgs();

        // Need to register handler manually because cannot inject domain-events config
        $receiverService1->registerHandler(TestEventHandler::class);
        $receiverService2->registerHandler(TestEventHandler::class);

        $receiverService1->processEventsQueue();
        $receiverService2->processEventsQueue();

        $handler->shouldHaveReceived('handle');
    }

}