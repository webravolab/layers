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

    public function __construct(DataStoreServiceInterface $dataStoreService, HydratorInterface $hydrator = null, $entity_name = 'DataStoreEventEntity', $entity_classname = 'Webravo\Common\Entity\DataStoreEventEntity') {
        // Inject in AbstractDataStoreTable the default Entity to manage Events
        parent::__construct($dataStoreService, $hydrator, $entity_name, $entity_classname);
    }

    public function persistEntity(AbstractEntity $entity) {

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
        $key = $this->dataStoreService->getConnection()->key($this->entity_name, $guid);

        // Create an entity
        $dsObject = $this->dataStoreService->getConnection()->entity($key);
        foreach($entity_data as $attribute => $value) {
            $dsObject[$attribute] = $value;
        }
        $version = $this->dataStoreService->getConnection()->insert($dsObject);
    }

    public function persist($payload) {
        // Cannot implement raw payload store
        throw new \Exception('Unimplemented');
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