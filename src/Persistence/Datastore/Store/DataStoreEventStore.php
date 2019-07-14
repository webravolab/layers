<?php

namespace Webravo\Persistence\Datastore\Store;

use Webravo\Application\Event\GenericEvent;
use Webravo\Infrastructure\Library\DependencyBuilder;
use Webravo\Infrastructure\Repository\EventStoreInterface;
use Webravo\Persistence\Datastore\DataTable\EventDataStoreTable;
use Webravo\Application\Event\EventInterface;
use Webravo\Common\Entity\DataStoreEventEntity;
use Webravo\Persistence\Eloquent\Hydrators\EventHydrator;

/**
 * The "Event Store" is a simply event bucket sink to log all events
 * It is NOT used for event dispatching or process
 */

class DataStoreEventStore implements EventStoreInterface {

    private $dataStoreService;

    public function __construct()
    {
        $this->dataStoreService = DependencyBuilder::resolve('Webravo\Infrastructure\Service\DataStoreServiceInterface');
    }

    public function Append(EventInterface $domainEvent)
    {
        $a_values = $domainEvent->toArray();
        $serialized_event = $domainEvent->getSerializedEvent();
        $e_event = DataStoreEventEntity::buildFromArray($a_values);
        $e_event->setPayload($serialized_event);
        $entity_name = get_class($e_event);
        $hydrator = new EventHydrator();
        $eventDataTable = new EventDataStoreTable($this->dataStoreService, $hydrator);
        $eventDataTable->persistEntity($e_event);
   }

    public function AllEvents()
    {
        // TODO: Implement AllEvents() method.
        throw new \Exception('Unimplemented');
    }

    public function getByGuid($guid): ?EventInterface
    {
        $hydrator = new EventHydrator();
        $eventDataTable = new EventDataStoreTable($this->dataStoreService, $hydrator);
        $a_event = $eventDataTable->getByGuid($guid);
        $a_encapsulated_event = $a_event['payload'];
        $event = GenericEvent::buildFromArray($a_encapsulated_event);
        return $event;
    }
}