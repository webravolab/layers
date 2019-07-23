<?php

use Webravo\Infrastructure\Library\Configuration;
use Webravo\Persistence\Eloquent\Store\EloquentEventStore;
use Webravo\Application\Event\EventBusDispatcher;
use Webravo\Application\Event\EventBucketBusMiddleware;
use Webravo\Persistence\Datastore\Store\DataStoreEventStore;
use Webravo\Persistence\Service\RabbitMQService;
use tests\TestProject\Domain\Events\TestEvent;

class EventBusTest extends TestCase
{

    public function testRabbitMQEventBus() {

        $publisherService = new RabbitMQService();
        $publisherService->createChannel('fanout', 'fanout-bind-exchange');

        $subscriberService1 = new RabbitMQService();
        $subscriberService1->createChannel('fanout', 'fanout-bind-exchange');
        $subscriberService1->createQueue('test-event-bus');
        $subscriberService1->subscribeQueue('test-event-bus', 'fanout-bind-exchange');

        $event = new TestEvent();

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
