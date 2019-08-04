<?php

namespace Webravo\Persistence\BigQuery\Store;

use Webravo\Application\Event\GenericEvent;
use Webravo\Infrastructure\Library\DependencyBuilder;
use Webravo\Infrastructure\Repository\EventRepositoryInterface;
use Webravo\Persistence\BigQuery\DataTable\EventBigQueryTable;
use Webravo\Application\Event\EventInterface;
use Webravo\Common\Entity\EventEntity;
use Webravo\Persistence\Hydrators\EventHydrator;

/**
 * The "Event Store" is a simply event bucket sink to log all events
 * It is NOT used for event dispatching or process
 */

class BigQueryEventStore implements EventRepositoryInterface {

    private $bigQueryService;

    public function __construct()
    {
        $this->bigQueryService = DependencyBuilder::resolve('Webravo\Infrastructure\Service\BigQueryServiceInterface');
    }

    public function append(EventInterface $domainEvent)
    {
        $a_values = $domainEvent->toArray();
        $serialized_event = $domainEvent->getSerializedEvent();
        $e_event = EventEntity::buildFromArray($a_values);
        $e_event->setPayload($serialized_event);
        $entity_name = get_class($e_event);
        $hydrator = new EventHydrator();
        $eventDataTable = new EventBigQueryTable($this->bigQueryService, $hydrator);
        $eventDataTable->persistEntity($e_event);
   }

    public function getByGuid(string $guid): ?EventInterface
    {
        $hydrator = new EventHydrator();
        $eventDataTable = new EventBigQueryTable($this->bigQueryService, $hydrator);
        $a_event = $eventDataTable->getByGuid($guid);
        $a_encapsulated_event = $a_event['payload'];
        $event = GenericEvent::buildFromArray($a_encapsulated_event);
        return $event;
    }

    public function AllEvents()
    {
        // TODO: Implement AllEvents() method.
        throw new \Exception('Unimplemented');
    }

}