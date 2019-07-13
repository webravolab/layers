<?php
namespace Webravo\Persistence\Datastore\DataTable;

use Webravo\Common\Entity\AbstractEntity;
use Webravo\Common\Contracts\StoreInterface;
use Webravo\Persistence\Repository\AbstractDataStoreTable;
use Webravo\Infrastructure\Service\DataStoreServiceInterface;

use DateTimeInterface;

class CommandDataStoreTable extends AbstractDataStoreTable implements StoreInterface {

    protected $id;
    protected $jobName;
    protected $channel;
    protected $payload;
    protected $header;
    protected $created_at;

    public function __construct(DataStoreServiceInterface $dataStoreService, $entity_name = 'DataStoreCommandEntity', $entity_classname = 'Webravo\Common\Entity\DataStoreCommandEntity') {
        // Inject in AbstractDataStoreTable the default Entity to manage Events
        parent::__construct($dataStoreService, $entity_name, $entity_classname);
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