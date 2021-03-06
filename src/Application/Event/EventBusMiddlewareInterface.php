<?php

namespace Webravo\Application\Event;

use Webravo\Application\Event\EventInterface;

/**
 * Interface EventBusMiddlewareInterface
 * More middlewares could be added to the Bus chain using the "Decorator Pattern"
 * @package Webravo\Application\Event
 */
interface EventBusMiddlewareInterface {

    /**
     * Register an handler to subscribe an event
     * @param $handler
     */
    public function subscribe($handler): void;

    /**
     * Dispatch an event to all registered handlers
     * @param EventInterface $event
     */
    public function dispatch(EventInterface $event): void;

}
