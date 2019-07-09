<?php
namespace Webravo\Application\Event;

use Webravo\Common\Contracts\DomainEventInterface;
use Psr\Log\LoggerInterface;
use Webravo\Infrastructure\Repository\EventStoreInterface;

/**
 * Class EventBucketBusMiddleware
 * use the "Decorator Pattern" to add a Event Storage level to Event Bus chain
 * @package Webravo\Application\Event
 */
class EventBucketBusMiddleware implements EventBusMiddlewareInterface {

    private $next;              // The next level in the Event Bus Chain
    private $eventStore;

    public function __construct(?EventBusMiddlewareInterface $next, EventStoreInterface $store) {
        $this->next = $next;
        $this->eventStore = $store;
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
        if (!is_null($this->eventStore)) {
            // Persist event in the Event Store
            $this->eventStore->Append($event);
        }
        if (!is_null($this->next)) {
            // Dispatch to the next middleware on stack
            $this->next->dispatch($event);
        }
    }
}
