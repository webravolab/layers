<?php
namespace Webravo\Persistence\Datastore\DataTable;

use Webravo\Common\Contracts\HydratorInterface;
use Webravo\Common\Entity\AbstractEntity;
use Webravo\Common\Contracts\StoreInterface;
use Webravo\Persistence\DataStore\DataTable\AbstractGdsStore;
use Webravo\Infrastructure\Service\DataStoreServiceInterface;

use DateTimeInterface;

class EventDataStoreTable extends AbstractGdsStore implements StoreInterface {

    protected $id;
    protected $type;
    protected $occurred_at;
    protected $payload;

    public function __construct(DataStoreServiceInterface $dataStoreService, HydratorInterface $hydrator = null, $entity_name = 'EventEntity', $entity_classname = 'Webravo\Common\Entity\EventEntity') {
        // Inject in AbstractGdsStore the default Entity to manage Events
        parent::__construct($dataStoreService, $hydrator, $entity_name, $entity_classname);
        // Exclude payload attribute from indexing to avoid 1500 characters limit
        $this->setExcludedFromIndex(['payload']);
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