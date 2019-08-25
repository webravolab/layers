<?php

namespace Webravo\Common\Domain;

use Webravo\Application\Event\EventInterface;

abstract class EventSourcedAggregateRoot implements AggregateRootInterface
{

    abstract function setAggregateId($aggregate_id);

    abstract function getAggregateId();

}
