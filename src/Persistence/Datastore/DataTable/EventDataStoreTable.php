<?php
namespace Webravo\Persistence\Datastore\DataTable;

use Webravo\Persistence\Repository\AbstractDataStoreTable;
use Webravo\Infrastructure\Service\DataStoreServiceInterface;


class EventDataStoreTable extends AbstractDataStoreTable {

    protected $id;
    protected $type;
    protected $occurred_at;
    protected $payload;

    public function __construct(DataStoreServiceInterface $dataStoreService, $entity_name = 'DataStoreEventEntity', $entity_classname = 'Webravo\Common\Entity\DataStoreEventEntity') {
        // Inject in AbstractDataStoreTable the default Entity to manage Events
        parent::__construct($dataStoreService, $entity_name, $entity_classname);
    }

    // All basic functions are implemented by AbstractDataStoreTable

    // Getters & Setters
    public function setType($type) {
        $this->type = $type;
    }

    public function getType() {
        return $this->type;
    }

    public function setOccurredAt($occurred_at) {
        $this->occurred_at = $occurred_at;
    }

    public function getOccurred_at() {
        return $this->occurred_at;
    }

    public function setPayload($payload) {
        $this->payload = $payload;
    }

    public function getPayload() {
        return $this->payload;
    }

}