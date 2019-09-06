<?php
namespace Webravo\Application\Event;

use Webravo\Application\Event\EventInterface;
use Psr\Log\LoggerInterface;

/**
 * Class EventLoggerBusMiddleware
 * use the "Decorator Pattern" to add a Event Logging capabilities to Event Bus chain
 * @package Webravo\Application\Event
 */
class EventLoggerBusMiddleware implements EventBusMiddlewareInterface {

    private $next;              // The next level in the Event Bus Chain
    private $loggerService;

    public function __construct(?EventBusMiddlewareInterface $next,  LoggerInterface $loggerService = null) {
        $this->next = $next;
        $this->loggerService = $loggerService;
    }

    public function subscribe($handler):void
    {
        if (!is_null($this->next)) {
            // Invoke next stack level subscriber
            $this->next->subscribe($handler);
        }
    }

    public function subscribeHandlerMapper(array $mapper, $handler_instance): void
    {
        if (!is_null($this->next)) {
            // Invoke next stack level subscriber
            $this->next->subscribeHandlerMapper($mapper, $handler_instance);
        }
    }

    public function dispatch(EventInterface $event, $topic = null):void
    {

        if (!is_null($this->loggerService)) {
            $this->loggerService->debug('Fire event: ' . $event->getType());
        }
        if (!is_null($this->next)) {
            $this->next->dispatch($event, $topic);
        }
    }
}
