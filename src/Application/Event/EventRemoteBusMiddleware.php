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
        if ($this->queueService && empty($this->queueService->getDefaultQueue())) {
            $event_queue = Configuration::get('EVENT_QUEUE',null, 'event-bus');
            $this->queueService->setDefaultQueue($event_queue);
        }
        /*
        if ($this->queueService) {
            $event_queue = Configuration::get('EVENT_QUEUE',null, 'event-bus');
            $this->queueService->createChannel('fanout', 'event-bus');
            $this->queueService->createQueue($event_queue, 'event-bus');
        }
        */
    }

    public function subscribe($handler):void
    {
        if (!is_null($this->next)) {
            // Invoke next stack level subscriber
            $this->next->subscribe($handler);
        }
    }

    public function subscribeHandlerMapper(array $mapper): void
    {
        if (!is_null($this->next)) {
            // Invoke next stack level subscriber
            $this->next->subscribeHandlerMapper($mapper);
        }
    }

    public function dispatch(EventInterface $event):void
    {
        if (!is_null($this->queueService)) {
            // If remote event queue is available, dispatch to remote queue
            $payload = $event->toArray();
            $json_payload = json_encode($payload);
            // $payload = $event->getSerializedPayload();
            $this->queueService->publishMessage($json_payload);
        }
        if (!is_null($this->next)) {
            // Dispatch to the next middleware on stack
            $this->next->dispatch($event);
        }
    }
}
