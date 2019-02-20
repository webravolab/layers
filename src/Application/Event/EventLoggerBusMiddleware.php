<?php
namespace Webravo\Application\Event;

use Webravo\Common\Contracts\DomainEventInterface;
use Psr\Log\LoggerInterface;

class EventLoggerBusMiddleware implements EventBusMiddlewareInterface {

    private $next;
    private $loggerService;

    public function __construct(EventBusMiddlewareInterface $next,  LoggerInterface $loggerService = null) {
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

    public function dispatch(EventInterface $event):void
    {

        if (!is_null($this->loggerService)) {
            $this->loggerService->debug('Fire event: ' . $event->getType());
        }

        $this->next->dispatch($event);

        /*
        if (!is_null($this->loggerService)) {
            $this->loggerService->debug('After event: ' . $event->getType());
        }
        */
    }
}
