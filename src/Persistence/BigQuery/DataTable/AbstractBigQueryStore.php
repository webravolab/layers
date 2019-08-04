<?php

namespace Webravo\Persistence\BigQuery\DataTable;

use Webravo\Common\Contracts\HydratorInterface;
use Webravo\Common\Contracts\StoreInterface;
use Webravo\Common\Entity\AbstractEntity;
use Webravo\Infrastructure\Service\BigQueryServiceInterface;
use Exception;

abstract class AbstractBigQueryStore implements StoreInterface {

    protected $bigQueryService;
    protected $hydrator;
    protected $bg_dataset_name;
    protected $bg_entity_name;
    protected $entity_name;
    protected $entity_classname;

    public function __construct(BigQueryServiceInterface $bigQueryService, HydratorInterface $hydrator = null, $entity_name = null, $entity_classname = null, $bg_entity_name = null, $bg_dataset_name = null) {
        $this->bigQueryService = $bigQueryService;
        $this->hydrator = $hydrator;
        if (!empty($entity_name)) {
            $this->entity_name = $entity_name;
            $this->bg_entity_name = $entity_name;
        }
        if (!empty($entity_classname)) {
            $this->entity_classname = $entity_classname;
        }
        if (!empty($bg_entity_name)) {
            $this->bg_entity_name = $bg_entity_name;
        }
        if (!empty($bg_dataset_name)) {
            $this->bg_dataset_name = $bg_dataset_name;
        }

        $dataset = $this->bigQueryService->getDataset($this->bg_dataset_name);
        if (!$dataset) {
            // Create Dataset
            $dataset = $this->bigQueryService->createDataset($this->bg_dataset_name);
        }
        $table = $this->bigQueryService->getTable($this->bg_dataset_name, $this->bg_entity_name);
        if (!$table) {
            if (method_exists($hydrator, "getSchema")) {
                $schema = $hydrator->getSchema();
                $table = $this->bigQueryService->createTable($this->bg_dataset_name, $this->bg_entity_name, $schema);
            }
            else {
                throw new Exception('[AbstractBigQueryStore] table ' . $this->bg_entity_name . ' does not exists and cannot be created');
            }
        }
    }

    public function getByGuid(string $guid)
    {
        $a_attributes = $this->getObjectByGuid($guid);
        $a_properties = $this->hydrator->hydrateDatastore($a_attributes);
        return $a_properties;
    }

    public function getObjectByGuid(string $guid)
    {
        $a_row= $this->bigQueryService->getByKey($this->bg_dataset_name, $this->bg_entity_name, 'guid', $guid, true);
        if (is_array($a_row)) {
            return $a_row;
        }
        return null;
    }

    public function append(array $a_properties)
    {
        if ($this->hydrator) {
            // If an Hydrator is set ... use it to map properties between Domain entity and DataStore entity
            $a_properties = $this->hydrator->mapDatastore($a_properties);
        }
        if (!isset($a_properties['guid']) || empty($a_properties['guid'])) {
            throw new Exception('[AbstractBigQueryStore][append] empty guid');
        }
        $guid = $a_properties['guid'];

        $this->bigQueryService->insertRow($this->bg_dataset_name, $this->bg_entity_name, $a_properties);
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
            throw new Exception('[AbstractBigQueryStore][' . $this->entity_name . '][update] empty guid');
        }
        $guid = $a_properties['guid'];

        $success = $this->bigQueryService->updateRow($this->bg_dataset_name, $this->bg_entity_name, $a_properties, 'guid');
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
        $success = $this->bigQueryService->deleteRow($this->bg_dataset_name, $this->bg_entity_name, $guid, 'guid');
    }

    public function getAllByKey($key, $value): array
    {
        $a_rows = $this->bigQueryService->getByKey($this->bg_dataset_name, $this->bg_entity_name, $key, $value);
        $entities = [];
        foreach ($a_rows as $a_attributes) {
            $a_properties = $this->hydrator->hydrateDatastore($a_attributes);
            $entities[] = $a_properties;
        }
        return $entities;
    }

    public function paginateByKey($key, $comparison, $value, $order, $pageSize, $pageCursor = ''): array
    {
        $entities = [];
        $results = $this->bigQueryService->paginateByKey($this->bg_dataset_name, $this->bg_entity_name, $key, $comparison, $value, $order, $pageSize, $pageCursor);
        if (is_array($results) && isset($results['entities'])) {
            foreach ($results['entities'] as $a_attributes) {
                $a_properties = $this->hydrator->hydrateDatastore($a_attributes);
                $entities[] = $a_properties;
            }
        }
        return array(
            'page_cursor' => $results['page_cursor'],
            'entities' => $entities
        );
    }

}