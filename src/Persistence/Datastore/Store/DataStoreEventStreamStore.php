<?php

namespace Webravo\Persistence\Datastore\Store;

use Webravo\Application\Event\EventStream;
use Webravo\Application\Event\GenericEvent;
use Webravo\Common\Entity\AggregateDomainEventEntity;
use Webravo\Infrastructure\Library\DependencyBuilder;
use Webravo\Infrastructure\Repository\EventStreamRepositoryInterface;
use Webravo\Persistence\Datastore\DataTable\AggregateDomainEventDataStoreTable;
use Webravo\Persistence\Datastore\DataTable\EventDataStoreTable;
use Webravo\Application\Event\EventInterface;
use Webravo\Persistence\Hydrators\EventStreamHydrator;

class DataStoreEventStreamStore implements EventStreamRepositoryInterface {

    private $dataStoreService;
    private $hydrator;

    public function __construct()
    {
        $this->dataStoreService = DependencyBuilder::resolve('Webravo\Infrastructure\Service\DataStoreServiceInterface');
        $this->hydrator = new EventStreamHydrator();
    }

    public function getEventStreamByAggregateId($aggregate_type, $aggregate_id): ?EventStream
    {
        $eventDataTable = new AggregateDomainEventDataStoreTable($this->dataStoreService, $this->hydrator, $aggregate_type);
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
            $eventDataTable = new AggregateDomainEventDataStoreTable($this->dataStoreService, $this->hydrator, $aggregate_type);
            $eventDataTable->persistEntity($e_event);
        }
    }

    public function persistStream(EventStream $stream): void {
        $aggregate_type = $stream->getAggregateType();
        $aggregate_id = $stream->getAggregateId();
        $this->addStreamToAggregateId($stream, $aggregate_type, $aggregate_id);
    }

}