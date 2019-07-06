<?php
namespace Webravo\Application\Event;

use Webravo\Application\Event\EventInterface;
use Webravo\Infrastructure\Service\QueueServiceInterface;
use Webravo\Infrastructure\Library\Configuration;

/**
 * Class EventRemoteBusMiddleware
 * use the "Decorator Pattern" to add a Event Remote Dispatch capabilities to Event Bus chain
 * @package Webravo\Application\Event
 */
class EventRemoteBusMiddleware implements EventBusMiddlewareInterface {

    private $next;              // The next level in the Event Bus Chain
    private $queueService;

    public function __construct(?EventBusMiddlewareInterface $next,  ?QueueServiceInterface $queueService) {
        $this->next = $next;
        $this->queueService = $queueService;
        if ($this->queueService) {
            $event_queue = Configuration::get('EVENT_QUEUE',null, 'event-bus');
            $this->queueService->createChannel('fanout', 'event-bus');
            $this->queueService->createQueue($event_queue, 'event-bus');
        }
    }

    public function subscribe($handler):void
    {
        if (!is_null($this->next)) {
            // Invoke next stack level subscriber
            $this->next->subscribe($handler);
        }
    }

    public function dispatch(EventInterface $event):void
    {
        if (!is_null($this->queueService)) {
            // If remote event queue is available, dispatch to remote queue
            $payload = $event->getSerializedPayload();
            $this->queueService->publishMessage($payload);
        }
        if (!is_null($this->next)) {
            // Dispatch to the next middleware on stack
            $this->next->dispatch($event);
        }
    }
}
