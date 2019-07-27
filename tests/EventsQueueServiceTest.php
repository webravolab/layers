<?php

use tests\TestProject\Domain\Events\TestEvent;
use tests\TestProject\Domain\Events\TestEventHandler;
/*
use Tests\Commands\MyTestWithoutHandlerCommand;
use Tests\Events\MyTestEvent;
use Tests\Events\MyTestEventHandler;
*/
use Webravo\Application\Service\EventsQueueService;
use Webravo\Persistence\Service\StackDriverLoggerService;

// use Log;
// use App\EventsStore;
// use Mockery;

class EventsQueueServiceTest extends TestCase
{

    public function testQueue2SyncAndStore2DatastoreEventQueueService()
    {
        $service = new EventsQueueService([
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
        $service->processQueueEvents();

        $handler->shouldHaveReceived('handle')->withArgs([$event]);

        // $loggerSpy->shouldHaveReceived('debug')->withArgs(['Fire event: ' . $event->getName()]);

    }
}