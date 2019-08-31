<?php

namespace Webravo\Infrastructure\Repository;

use Webravo\Common\Contracts\RepositoryInterface;
use Webravo\Application\Event\EventStream;

interface EventStreamRepositoryInterface
{
    public function getEventStreamByAggregateId($aggregate_type, $aggregate_id): ?EventStream;

    public function addStreamToAggregateId(EventStream $stream, $aggregate_type = null, $aggregate_id = null): void;

    public function persistStream(EventStream $stream): void;

}
