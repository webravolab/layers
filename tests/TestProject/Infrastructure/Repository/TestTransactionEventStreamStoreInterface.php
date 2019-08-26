<?php
namespace tests\TestProject\Infrastructure\Repository;


use Webravo\Application\Event\AggregateDomainEvent;
use Webravo\Application\Event\EventStream;

interface TestTransactionEventStreamStoreInterface
{
    // Add here any additional methods specific to the store

    public function getEventsByAggregateId($aggregate_id);

    public function persist(EventStream $stream): void;

    public function addEvent($aggregate_id, $event): void;

}
