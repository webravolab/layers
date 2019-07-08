<?php
namespace Webravo\Persistence\Datastore\DataTable;

use Webravo\Common\Entity\AbstractEntity;
use Webravo\Infrastructure\Repository\StorableInterface;
use Webravo\Persistence\Repository\AbstractDataStoreTable;
use Webravo\Infrastructure\Service\DataStoreServiceInterface;

use DateTimeInterface;

class EventDataStoreTable extends AbstractDataStoreTable implements StorableInterface {

    protected $id;
    protected $type;
    protected $occurred_at;
    protected $payload;

    public function __construct(DataStoreServiceInterface $dataStoreService, $entity_name = 'DataStoreEventEntity', $entity_classname = 'Webravo\Common\Entity\DataStoreEventEntity') {
        // Inject in AbstractDataStoreTable the default Entity to manage Events
        parent::__construct($dataStoreService, $entity_name, $entity_classname);
    }

    public function persist(AbstractEntity $entity) {

        $a_name = get_class($entity);
        $b = new $a_name;
        if (method_exists($entity, "toSerializedArray")) {
            $entity_data = $entity->toSerializedArray();
        }
        else {
            $entity_data = $entity->toArray(); // $this->hydrator->Extract($entity);
        }
        $guid = $entity->getGuid();

        // Create key based on guid
        $key = $this->dataStoreService->connection()->key($this->entity_name, $guid);

        // Create an entity
        $dsObject = $this->dataStoreService->connection()->entity($key);
        foreach($entity_data as $attribute => $value) {
            $dsObject[$attribute] = $value;
        }
        $version = $this->dataStoreService->connection()->insert($dsObject);
    }

    // All basic functions are implemented by AbstractDataStoreTable

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