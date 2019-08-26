<?php
namespace tests\TestProject\Persistence\Memory;

use \InvalidArgumentException;
use tests\TestProject\Infrastructure\Repository\TestTransactionEventStreamStoreInterface;
use tests\TestProject\Infrastructure\Repository\TestTransactionStoreInterface;
use Webravo\Application\Event\AggregateDomainEvent;
use Webravo\Application\Event\EventStream;
use Webravo\Common\Entity\AbstractEntity;

class TestTransactionEventStoreMemoryStore implements TestTransactionEventStreamStoreInterface
{
    static $storage = [];

    public function getEventsByAggregateId($aggregate_id)
    {
        // TODO: Implement getEventsByAggregateId() method.
        if (!isset(self::$storage[$aggregate_id])) {
            throw new InvalidArgumentException("[TestTransactionEventStoreMemoryStore][getEventsByAggregateId] stream with aggregate_id $aggregate_id does not exists");
        }
        $a_stream = (object) self::$storage[$aggregate_id];
        return (array)clone $a_stream;
    }


    public function persist(EventStream $stream): void
    {
        $aggregate_id = $stream->getAggregateId();
        if (!$aggregate_id) {
            // Exception
        }
        if (isset(self::$storage[$aggregate_id])) {
            // Clear previous stream
            unset(self::$storage[$aggregate_id]);
        }
        self::$storage[$aggregate_id] = [];
        foreach ($stream as $event) {
            if ($event instanceof AggregateDomainEvent) {
                $aggregate_type = $event->getAggregateType();   // TODO
                $a_event = $event->toArray();
                $version = $event->getVersion();
                self::$storage[$aggregate_id][$version] = $a_event;
            } else {
                // Exception
            }
        }
    }

    public function addEvent($aggregate_id, $event): void
    {
        if (!isset(self::$storage[$aggregate_id])) {
            // Exception
        }
        if ($event instanceof AggregateDomainEvent) {
            $aggregate_type = $event->getAggregateType();   // TODO
            $a_event = $event->toArray();
            $version = $event->getVersion();
            if (isset(self::$storage[$aggregate_id][$version])) {
                // Exception
            }
            self::$storage[$aggregate_id][$version] = $a_event;
        }
        else {
            // Exception
        }
    }
}
