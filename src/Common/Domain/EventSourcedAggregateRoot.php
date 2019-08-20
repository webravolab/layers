<?php

namespace Webravo\Common\Domain;

use Webravo\Application\Event\EventInterface;

abstract class EventSourcedAggregateRoot implements AggregateRootInterface
{

    private $eventMap = [];

    public function apply(EventInterface $event)
    {
        // TODO
    }

}
