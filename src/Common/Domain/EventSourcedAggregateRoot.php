<?php

namespace Webravo\Common\Domain;

use Webravo\Application\Event\EventInterface;
use Webravo\Common\Entity\AbstractEntity;

abstract class EventSourcedAggregateRoot extends AbstractEntity implements AggregateRootInterface
{

    abstract function setAggregateId($aggregate_id);

    abstract function getAggregateId();

}
