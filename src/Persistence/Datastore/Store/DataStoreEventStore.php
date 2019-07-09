<?php

namespace Webravo\Persistence\Datastore\Store;

use Webravo\Application\Event\GenericEvent;
use Webravo\Infrastructure\Library\DependencyBuilder;
use Webravo\Infrastructure\Repository\EventStoreInterface;
use Webravo\Persistence\Datastore\DataTable\EventDataStoreTable;
use Webravo\Common\Contracts\DomainEventInterface;
use Webravo\Common\Entity\DataStoreEventEntity;
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

    public function Append(DomainEventInterface $domainEvent)
    {
        $a_values = $domainEvent->toArray();
        $e_event = DataStoreEventEntity::buildFromArray($a_values);
        $entity_name = get_class($e_event);
        $eventDataTable = new EventDataStoreTable($this->dataStoreService);
        $eventDataTable->persistEntity($e_event);
   }

    public function AllEvents()
    {
        // TODO: Implement AllEvents() method.
        throw new \Exception('Unimplemented');
    }

    public function getByGuid($guid): ?DomainEventInterface
    {
        $eventDataTable = new EventDataStoreTable($this->dataStoreService);
        $a_event = $eventDataTable->getByGuid($guid);
        $event = GenericEvent::buildFromArray($a_event->toArray());
        return $event;
    }
}