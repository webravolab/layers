<?php

namespace Webravo\Common\Domain;

use Webravo\Application\Event\EventInterface;

abstract class EventSourcedAggregateRoot implements AggregateRootInterface
{
    // use EventSourcedTrait;

    /*
    private $eventMap = [];

    public function apply(EventInterface $event)
    {
        $applier = $this->eventMap[get_class($event)] ?? null;
        if ($applier) {
            return $this->$applier($event);
        }
    }
    */
}
