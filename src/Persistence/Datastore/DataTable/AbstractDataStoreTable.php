<?php

namespace Webravo\Persistence\DataStore\DataTable;

use Webravo\Common\Contracts\StoreInterface;
use Webravo\Infrastructure\Service\DataStoreServiceInterface;
use Webravo\Common\Entity\AbstractEntity;
use Webravo\Infrastructure\Repository\HydratorInterface;
use Webravo\Infrastructure\Library\DependencyBuilder;


use ReflectionClass;

abstract class AbstractDataStoreTable implements StoreInterface {

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
        $key = $this->dataStoreService->getConnection()->key($this->entity_name, $guid);
        $dsObject = $this->dataStoreService->getConnection()->lookup($key);
        return $dsObject;
    }

    public function update(AbstractEntity $entity) {
        // $entity_name = get_class($entity);
        $entity_data = $entity->toArray();
        $guid = $entity->getGuid();

        // Create key based on guid
        $key = $this->dataStoreService->getConnection()->key($this->entity_name, $guid);

        $dsObject = $this->getObjectByGuid($guid);
        if (!is_null($dsObject)) {
            foreach($entity_data as $attribute => $value) {
                $dsObject[$attribute] = $value;
            }
            $version = $this->dataStoreService->getConnection()->update($dsObject);
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
        $key = $this->dataStoreService->getConnection()->key($this->entity_name, $guid);
        // Delete entity
        $version = $this->dataStoreService->getConnection()->delete($key);
    }
}