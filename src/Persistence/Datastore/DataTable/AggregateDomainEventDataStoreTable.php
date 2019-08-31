<?php
namespace Webravo\Persistence\Datastore\DataTable;

use Webravo\Common\Contracts\HydratorInterface;
use Webravo\Common\Entity\AbstractEntity;
use Webravo\Common\Contracts\StoreInterface;
use Webravo\Persistence\DataStore\DataTable\AbstractGdsStore;
use Webravo\Infrastructure\Service\DataStoreServiceInterface;

use DateTimeInterface;

class AggregateDomainEventDataStoreTable extends AbstractGdsStore implements StoreInterface {

    protected $id;
    protected $type;
    protected $occurred_at;
    protected $payload;

    public function __construct(DataStoreServiceInterface $dataStoreService, $aggregate_type) {
        // Inject in AbstractGdsStore the default Entity to manage Events
        $hydrator = null;
        $entity_name = 'EventSource' . $aggregate_type;
        $entity_classname = null;
        parent::__construct($dataStoreService, $hydrator, $entity_name, $entity_classname);
        // Exclude payload attribute from indexing to avoid 1500 characters limit
        $this->setExcludedFromIndex(['payload']);
    }

    public function getEventsByAggregateId($aggregate_id): array
    {
        $query = $this->dataStoreService->getConnection()->query()
            ->kind($this->gds_entity_name)
            ->filter('aggregate_id', '=', $aggregate_id)
            ->order('version');
        $result = $this->dataStoreService->getConnection()->runQuery($query);
        $entities = [];
        foreach ($result as $entity) {
            $nextPageCursor = $entity->cursor();
            $a_attributes = $entity->get();
            if ($this->hydrator) {
                // Use hydrator if set
                $a_properties = $this->hydrator->hydrateDatastore($a_attributes);
                $entities[] = $a_properties;
            }
            else {
                // Return raw data
                $entities[] = $a_attributes;
            }
        }
        return $entities;
    }
    // All basic functions are implemented by AbstractGdsStore

    // Getters & Setters
    public function setType($type) {
        $this->type = $type;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setOccurredAt(DateTimeInterface $occurred_at)
    {
        $this->occurred_at = $occurred_at;
    }

    public function getOccurred_at():DateTimeInterface
    {
        return $this->occurred_at;
    }

    public function setPayload($payload)
    {
        $this->payload = $payload;
    }

    public function getPayload()
    {
        return $this->payload;
    }
}