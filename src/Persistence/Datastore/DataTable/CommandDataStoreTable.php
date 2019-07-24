<?php
namespace Webravo\Persistence\Datastore\DataTable;

use Webravo\Common\Entity\AbstractEntity;
use Webravo\Common\Contracts\HydratorInterface;
use Webravo\Common\Contracts\StoreInterface;
use Webravo\Persistence\DataStore\DataTable\AbstractGdsStore;
use Webravo\Infrastructure\Service\DataStoreServiceInterface;

use DateTimeInterface;

class CommandDataStoreTable extends AbstractGdsStore implements StoreInterface {

    protected $id;
    protected $jobName;
    protected $channel;
    protected $payload;
    protected $header;
    protected $created_at;

    public function __construct(DataStoreServiceInterface $dataStoreService, HydratorInterface $hydrator = null, $entity_name = 'CommandEntity', $entity_classname = 'Webravo\Common\Entity\CommandEntity') {
        // Inject in AbstractGdsStore the default Entity to manage Commands
        parent::__construct($dataStoreService, $hydrator, $entity_name, $entity_classname);
    }

    /*
    public function persistEntity(AbstractEntity $entity) {

        $a_name = get_class($entity);
        $b = new $a_name;
        if (method_exists($entity, "toSerializedArray")) {
            $entity_data = $entity->toSerializedArray();
        }
        else {
            $entity_data = $entity->toArray();
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
    */

    // All basic functions are implemented by AbstractDataStoreTable

    // Getters & Setters

    public function setName($name) {
        $this->jobName = $name;
    }

    public function getName()
    {
        return $this->jobName;
    }

    public function setChannel($chanel) {
        $this->channel = $chanel;
    }

    public function getChannel()
    {
        return $this->channel;
    }

    public function setCreatedAt(DateTimeInterface $created_at)
    {
        $this->created_at = $created_at;
    }

    public function getCreatedAt():DateTimeInterface
    {
        return $this->created_at;
    }

    public function setHeader($header)
    {
        $this->header = $header;
    }

    public function getHeader()
    {
        return $this->header;
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