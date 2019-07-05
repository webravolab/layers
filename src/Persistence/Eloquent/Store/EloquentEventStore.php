<?php

namespace Webravo\Persistence\Eloquent\Store;

use Webravo\Application\Event\GenericEvent;
use Webravo\Common\Contracts\DomainEventInterface;
use Webravo\Infrastructure\Repository\EventStoreInterface;
use Webravo\Persistence\Eloquent\DataTable\EventDataTable;
use Webravo\Persistence\Eloquent\Hydrators\EventHydrator;

/**
 * The "Event Store" is a simply event bucket sink to log all events
 * It is NOT used for event dispatching or process
 */

// STORE USE ELOQUENT MODEL TO ACCESS DATA
// STORE RECEIVE/RETURN DATA ARRAY FROM/TO REPOSITORY
// STORE GET/SET ELOQUENT ATTRIBUTES FROM/TO ELOQUENT MODEL
// STORE USE HYDRATOR TO CONVERT DATA ARRAY TO ELOQUENT ATTRIBUTES (single or array)

class EloquentEventStore implements EventStoreInterface {

    public function Append(DomainEventInterface $domainEvent)
    {
        $hydrator = new EventHydrator();
        $eventDataTable = new EventDataTable($hydrator);
        $eventDataTable->persist($domainEvent);
   }

    public function AllEvents()
    {
        // TODO: Implement AllEvents() method.
        throw new \Exception('Unimplemented');
    }

    public function getByGuid($guid): ?DomainEventInterface
    {
        $a_event = EventDataTable::getByGuid($guid);
        $event = GenericEvent::buildFromArray($a_event);
        return $event;
    }
}