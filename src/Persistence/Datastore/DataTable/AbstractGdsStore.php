<?php

namespace Webravo\Persistence\DataStore\DataTable;

use Webravo\Common\Contracts\HydratorInterface;
use Webravo\Common\Contracts\StoreInterface;
use Webravo\Common\Entity\AbstractEntity;
use Webravo\Infrastructure\Service\DataStoreServiceInterface;
use Exception;
use Google\Cloud\Datastore\Query\Query;

abstract class AbstractGdsStore implements StoreInterface {

    protected $dataStoreService;
    protected $hydrator;
    protected $gds_entity_name;
    protected $entity_name;
    protected $entity_classname;
    protected $excluded_from_indexes = [];

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
        $key = $this->dataStoreService->getConnection()->key($this->gds_entity_name, $guid);
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
        if (count($this->excluded_from_indexes) > 0) {
            // Set attributes excluded by index
            $dsObject->setExcludeFromIndexes($this->excluded_from_indexes);
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

    /**
     * Set attributes names to exclude from entity indexing
     * @param array $excluded_attributes
     */
    public function setExcludedFromIndex(array $excluded_attributes): void
    {
        $this->excluded_from_indexes = $excluded_attributes;
    }


    public function getAllByKey($key, $value): array
    {
        $query = $this->dataStoreService->getConnection()->query()
            ->kind($this->gds_entity_name)
            ->filter($key, '=', $value);
        $result = $this->dataStoreService->getConnection()->runQuery($query);
        $entities = [];
        foreach ($result as $entity) {
            $nextPageCursor = $entity->cursor();
            $a_attributes = $entity->get();
            $a_properties = $this->hydrator->hydrateDatastore($a_attributes);
            $entities[] = $a_properties;
        }
        return $entities;
    }

    public function paginateByKey($key, $comparison, $value, $order, $pageSize, $pageCursor = ''): array
    {
        $query = $this->dataStoreService->getConnection()->query()
            ->kind($this->gds_entity_name)
            ->filter($key, $comparison, $value)
            ->order($key, $order == 'desc' ? Query::ORDER_DESCENDING : Query::ORDER_ASCENDING)
            ->limit($pageSize)
            ->start($pageCursor);

        $result = $this->dataStoreService->getConnection()->runQuery($query);
        $nextPageCursor = '';
        $entities = [];
        foreach ($result as $entity) {
            $nextPageCursor = $entity->cursor();
            $a_attributes = $entity->get();
            $a_properties = $this->hydrator->hydrateDatastore($a_attributes);
            $entities[] = $a_properties;
        }
        return array(
            'page_cursor' => $nextPageCursor,
            'entities' => $entities
        );
    }

}