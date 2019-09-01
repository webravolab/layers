<?php

namespace Webravo\Persistence\Eloquent\Store;

use Webravo\Application\Event\EventStream;
use Webravo\Application\Event\GenericEvent;
use Webravo\Application\Event\EventInterface;
use Webravo\Common\Entity\EventEntity;
use Webravo\Infrastructure\Repository\EventStreamRepositoryInterface;
use Webravo\Persistence\Eloquent\DataTable\EventDataTable;
use Webravo\Persistence\Hydrators\EventHydrator;
use Webravo\Common\Entity\AggregateDomainEventEntity;
use Webravo\Persistence\Eloquent\DataTable\AggregateDomainEventDataTable;
use Webravo\Persistence\Hydrators\EventStreamHydrator;

/**
 * The "Event Store" is a simply event bucket sink to log all events
 * It is NOT used for event dispatching or process
 */

// STORE USE ELOQUENT MODEL TO ACCESS DATA
// STORE RECEIVE/RETURN DATA ARRAY FROM/TO REPOSITORY
// STORE GET/SET ELOQUENT ATTRIBUTES FROM/TO ELOQUENT MODEL
// STORE USE HYDRATOR TO CONVERT DATA ARRAY TO ELOQUENT ATTRIBUTES (single or array)

class EloquentEventStreamStore implements EventStreamRepositoryInterface
{

    public function getEventStreamByAggregateId($aggregate_type, $aggregate_id): ?EventStream
    {
        $hydrator = new EventStreamHydrator();
        $eventDataTable = new AggregateDomainEventDataTable($hydrator, $aggregate_type);
        $a_event = $eventDataTable->getEventsByAggregateId($aggregate_id);
        $stream = EventStream::createByRawEvents($a_event);
        return $stream;
    }

    public function addStreamToAggregateId(EventStream $stream, $aggregate_type = null, $aggregate_id = null): void
    {
        // If aggregate_type ang aggregate_id are not given ... get them from the stream
        if (!$aggregate_type) {
            $aggregate_type = $stream->getAggregateType();
        }
        if (!$aggregate_id) {
            $aggregate_id = $stream->getAggregateId();
        }
        foreach($stream as $event) {
            $a_values = $event->toArray();
            $serialized_event = $event->getSerializedEvent();
            $e_event =  AggregateDomainEventEntity::buildFromArray($a_values);
            $e_event->setPayload($serialized_event);
            $hydrator = new EventStreamHydrator();
            $eventDataTable = new AggregateDomainEventDataTable($hydrator, $aggregate_type);
            $eventDataTable->persistEntity($e_event);
        }
    }

    public function persistStream(EventStream $stream): void {
        $aggregate_type = $stream->getAggregateType();
        $aggregate_id = $stream->getAggregateId();
        $this->addStreamToAggregateId($stream, $aggregate_type, $aggregate_id);
    }
}