<?php

namespace Webravo\Application\Event;

use Webravo\Application\Event\EventInterface;

interface EventBusMiddlewareInterface {

    public function subscribe($handler): void;

    public function dispatch(EventInterface $event): void;

}
