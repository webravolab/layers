<?php

namespace Webravo\Persistence\DataStore\DataTable;

use PHPUnit\Framework\ExpectationFailedException;
use Webravo\Common\Contracts\HydratorInterface;
use Webravo\Common\Contracts\StoreInterface;
use Webravo\Common\Entity\AbstractEntity;
use Webravo\Infrastructure\Service\DataStoreServiceInterface;
use Exception;

abstract class AbstractGdsStore implements StoreInterface {

    protected $dataStoreService;
    protected $hydrator;
    protected $gds_entity_name;
    protected $entity_name;
    protected $entity_classname;

    public function __construct(DataStoreServiceInterface $dataStoreService, HydratorInterface $hydrator = null, $entity_name = null, $entity_classname = null, $gds_entity_name = null) {
        $this->dataStoreService = $dataStoreService;
        $this->hydrator = $hydrator;
        if (!empty($entity_name)) {
            $this->entity_name = $entity_name;
            $this->gds_entity_name = $entity_name;
        }
        if (!empty($entity_classname)) {
            $this->entity_classname = $entity_classname;
        }
        if (!empty($gds_entity_name)) {
            $this->gds_entity_name = $gds_entity_name;
        }
    }

    public function getByGuid(string $guid)
    {
        $dsObject = $this->getObjectByGuid($guid);
        if (!is_null($dsObject)) {
            $a_attributes = $dsObject->get();
            $a_properties = $this->hydrator->hydrateDatastore($a_attributes);
            return $a_properties;
        }
        return null;
    }

    public function getObjectByGuid(string $guid)
    {
        $key = $this->dataStoreService->getConnection()->key($this->entity_name, $guid);
        $dsObject = $this->dataStoreService->getConnection()->lookup($key);
        return $dsObject;
    }

    public function append(array $a_properties)
    {
        if ($this->hydrator) {
            // If an Hydrator is set ... use it to map properties between Domain entity and DataStore entity
            $a_properties = $this->hydrator->mapDatastore($a_properties);
        }
        if (!isset($a_properties['guid']) || empty($a_properties['guid'])) {
            throw new Exception('[AbstractGdsStore][append] empty guid');
        }
        $guid = $a_properties['guid'];
        // Create key based on guid
        $key = $this->dataStoreService->getConnection()->key($this->gds_entity_name, $guid);

        // Create an entity
        $dsObject = $this->dataStoreService->getConnection()->entity($key);
        foreach($a_properties as $attribute => $value) {
            $dsObject[$attribute] = $value;
        }
        $version = $this->dataStoreService->getConnection()->insert($dsObject);
    }

    public function persistEntity(AbstractEntity $entity) {

        $a_name = get_class($entity);
        $b = new $a_name;
        if (method_exists($entity, "toSerializedArray")) {
            $entity_data = $entity->toSerializedArray();
        }
        else {
            $entity_data = $entity->toArray();
        }
        $this->append($entity_data);
        /*
        $guid = $entity->getGuid();

        // Create key based on guid
        $key = $this->dataStoreService->getConnection()->key($this->entity_name, $guid);

        // Create an entity
        $dsObject = $this->dataStoreService->getConnection()->entity($key);
        foreach($entity_data as $attribute => $value) {
            $dsObject[$attribute] = $value;
        }
        $version = $this->dataStoreService->getConnection()->insert($dsObject);
        */
    }

    public function update(array $a_properties)
    {
        if ($this->hydrator) {
            // If an Hydrator is set ... use it to map properties between Domain entity and DataStore entity
            $a_properties = $this->hydrator->mapDatastore($a_properties);
        }
        if (!isset($a_properties['guid']) || empty($a_properties['guid'])) {
            throw new Exception('[AbstractGdsStore][' . $this->entity_name . '][update] empty guid');
        }
        $guid = $a_properties['guid'];

        // Create key based on guid
        $key = $this->dataStoreService->getConnection()->key($this->gds_entity_name, $guid);

        $dsObject = $this->getObjectByGuid($guid);
        if (!is_null($dsObject)) {
            foreach($a_properties as $attribute => $value) {
                $dsObject[$attribute] = $value;
            }
            $version = $this->dataStoreService->getConnection()->update($dsObject);
        }
        else {
            throw new \Exception('[AbstractGdsStore][' . $this->entity_name . '][Update] Guid ' . $guid . ' does not exists');
        }
    }

    public function delete(array $a_properties)
    {
        // Don't need to use Hydrator... assume that "guid" is always present in properties
        if (isset($a_properties['guid'])) {
            $guid = $a_properties['guid'];
            $this->deleteByGuid($guid);
        }
    }

    public function deleteByGuid(string $guid)
    {
        // Create key based on guid
        $key = $this->dataStoreService->getConnection()->key($this->gds_entity_name, $guid);
        // Delete entity
        $version = $this->dataStoreService->getConnection()->delete($key);
    }
}