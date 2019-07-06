<?php

namespace Webravo\Persistence\Repository;

use Webravo\Common\Entity\AbstractEntity;
use Webravo\Infrastructure\Repository\HydratorInterface;
use Webravo\Infrastructure\Library\DependencyBuilder;

use ReflectionClass;
use Webravo\Infrastructure\Service\DataStoreServiceInterface;

abstract class AbstractDataStoreTable {

    protected $dataStoreService;
    protected $entity_name;
    protected $entity_classname;

    public function __construct(DataStoreServiceInterface $dataStoreService, $entity_name = null, $entity_classname = null) {
        $this->dataStoreService = $dataStoreService;
        if (!empty($entity_name)) {
            $this->entity_name = $entity_name;
        }
        if (!empty($entity_classname)) {
            $this->entity_classname = $entity_classname;
        }
    }

    public function persist(AbstractEntity $entity) {

        $a_name = get_class($entity);
        $b = new $a_name;
        $entity_data = $entity->toArray();
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

    public function getByGuid($guid)
    {
        $dsObject = $this->getObjectByGuid($guid);
        if (!is_null($dsObject)) {
            $entity = new $this->entity_classname;
            $entity->fromArray($dsObject->get());
            return $entity;
        }
        return null;
    }

    public function getObjectByGuid($guid)
    {
        $key = $this->dataStoreService->connection()->key($this->entity_name, $guid);
        $dsObject = $this->dataStoreService->connection()->lookup($key);
        return $dsObject;
    }

    public function update(AbstractEntity $entity) {
        // $entity_name = get_class($entity);
        $entity_data = $entity->toArray();
        $guid = $entity->getGuid();

        // Create key based on guid
        $key = $this->dataStoreService->connection()->key($this->entity_name, $guid);

        $dsObject = $this->getObjectByGuid($guid);
        if (!is_null($dsObject)) {
            foreach($entity_data as $attribute => $value) {
                $dsObject[$attribute] = $value;
            }
            $version = $this->dataStoreService->connection()->update($dsObject);
        }
        else {
            throw new \Exception('[DataStoreTable][' . $this->entity_name . '][Update] Guid ' . $guid . ' does not exists');
        }
    }

    public function delete(AbstractEntity $entity)
    {
        $guid = $entity->getGuid();
        $this->deleteByGuid($guid);
    }

    public function deleteByGuid($guid)
    {
        // Create key based on guid
        $key = $this->dataStoreService->connection()->key($this->entity_name, $guid);
        // Delete entity
        $version = $this->dataStoreService->connection()->delete($key);
    }
}