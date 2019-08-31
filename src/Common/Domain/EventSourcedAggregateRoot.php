<?php

namespace Webravo\Common\Domain;

use Webravo\Common\Entity\AbstractEntity;
use Webravo\Application\Event\EventStream;
use Webravo\Application\Event\AggregateDomainEvent;

abstract class EventSourcedAggregateRoot extends AbstractEntity implements AggregateRootInterface
{

    abstract function setAggregateId($aggregate_id);

    abstract function getAggregateId();

    abstract function setEventStream(EventStream $stream);

    abstract function setSnapShotEventFrequency($frequency): void;

    abstract function getEventStream();

    abstract function initChangedStream(EventStream $stream);

    abstract function apply(AggregateDomainEvent $event);

    abstract function getChangedStream();

    abstract static function rebuildFromHistory(EventStream $stream);

    abstract function replay(EventStream $events);

    abstract function mutate(AggregateDomainEvent $event);

    abstract function getVersion();
}
