<?php

namespace Webravo\Common\Contract;

use Webravo\Common\Contracts\RepositoryInterface;
use Webravo\Application\Event\EventStream;

interface EventStreamRepositoryInterface
{
    public function getEventsByAggregateId($aggregate_id): ?EventStream;

    public function persist(EventStream $stream): void;

}
