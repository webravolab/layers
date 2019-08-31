<?php

namespace Webravo\Common\Contract;

use Webravo\Common\Contracts\RepositoryInterface;
use Webravo\Application\Event\EventStream;

interface EventStreamRepositoryInterface
{
    public function getEventsByAggregateId($aggregate_id): ?EventStream;

    public function addStreamToAggregateId(EventStream $stream, $aggregate_type = null, $aggregate_id = null): void;

    public function persist(EventStream $stream): void;

}
