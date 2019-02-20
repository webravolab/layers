<?php

namespace Webravo\Persistence\Eloquent\Store;

use Webravo\Common\Contracts\DomainEventInterface;
use Webravo\Infrastructure\Repository\EventStoreInterface;
use Webravo\Persistence\Eloquent\DataTable\EventDataTable;

// Eloquent Model
use App\Events;
use Webravo\Persistence\Eloquent\Hydrators\EventHydrator;

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
}