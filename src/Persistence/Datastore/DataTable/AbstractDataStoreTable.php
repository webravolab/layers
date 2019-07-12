<?php

namespace Webravo\Persistence\Repository;

use mysql_xdevapi\Exception;
use Webravo\Infrastructure\Repository\StorableInterface;
use Webravo\Infrastructure\Service\DataStoreServiceInterface;
use Webravo\Common\Entity\AbstractEntity;
use Webravo\Infrastructure\Repository\HydratorInterface;
use Webravo\Infrastructure\Library\DependencyBuilder;


use ReflectionClass;

abstract class AbstractDataStoreTable implements StorableInterface {

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

    public function persistEntity(AbstractEntity $entity) {

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

    public function persist($payload) {
        // Cannot implement raw payload store
        throw new \Exception('Unimplemented');
    }

    public function getByGuid($guid, $entity_name = null)
    {
        // TODO - find by guid without entity name
        if (!$entity_name) {
            $entity_name = $this->entity_classname;
        }
        $dsObject = $this->getObjectByGuid($guid, $entity_name);
        if (!is_null($dsObject)) {
            if ($dsObject->getProperty('class_name')) {
                $entity_classname = $dsObject->getProperty('class_name');
            }
            else {
                $entity_classname = $this->entity_classname;
            }
            if (class_exists($entity_classname)) {
                // TODO - Replace with dependency builder
                $entity = new $entity_classname;
                $entity->fromArray($dsObject->get());
                return $entity;
            }
            else {
                throw new Exception("[AbstractDataStoreTable][getByGuid] Cannot rebuild entity: $entity_classname");
            }
        }
        return null;
    }

    public function getObjectByGuid($guid, $entity_name = null)
    {
        if (!$entity_name) {
            $entity_name = $this->entity_classname;
        }
        $key = $this->dataStoreService->connection()->key($entity_name, $guid);
        $dsObject = $this->dataStoreService->connection()->lookup($key);
        // TODO - If not found search only by Guid (using GQL)
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