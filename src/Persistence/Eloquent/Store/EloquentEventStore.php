<?php

namespace Webravo\Persistence\Eloquent\Store;

use Webravo\Application\Event\GenericEvent;
use Webravo\Application\Event\EventInterface;
use Webravo\Common\Entity\EventEntity;
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

    public function Append(EventInterface $domainEvent)
    {
        /*
        $hydrator = new EventHydrator();
        $eventDataTable = new EventDataTable($hydrator);
        $eventDataTable->persist($domainEvent);
        */
        $a_values = $domainEvent->toArray();
        $serialized_event = $domainEvent->getSerializedEvent();
        $e_event = EventEntity::buildFromArray($a_values);
        $e_event->setPayload($serialized_event);
        $entity_name = get_class($e_event);
        $hydrator = new EventHydrator();
        $eventDataTable = new EventDataTable($hydrator);
        $eventDataTable->persistEntity($e_event);
   }


    public function getByGuid($guid): ?EventInterface
    {
        $hydrator = new EventHydrator();
        $eventDataTable = new EventDataTable($hydrator);
        $a_event = $eventDataTable->getByGuid($guid);
        $a_encapsulated_event = $a_event['payload'];
        $event = GenericEvent::buildFromArray($a_encapsulated_event);
        return $event;
    }
}