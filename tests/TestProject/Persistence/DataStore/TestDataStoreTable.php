<?php
namespace tests\TestProject\Persistence\DataStore;

use Webravo\Infrastructure\Library\DependencyBuilder;
use Webravo\Infrastructure\Service\DataStoreServiceInterface;
use Webravo\Persistence\DataStore\DataTable\AbstractGdsStore;
use tests\TestProject\Infrastructure\Repository\TestStoreInterface;

Class TestDataStoreTable extends AbstractGdsStore implements TestStoreInterface
{
    protected $entity_name = 'TestEntity';
    protected $entity_classname = 'tests\TestProject\Domain\Entity\TestEntity';
    protected $gds_entity_name = "TestEntity";

    public function __construct(DataStoreServiceInterface $dataStoreService, $entity_name = null, $entity_classname = null, $gds_entity_name = null)
    {
        if (is_null($dataStoreService)) {
            $dataStoreService = DependencyBuilder::resolve('Webravo\Infrastructure\Service\DataStoreServiceInterface');
        }
        if (!empty($entity_name)) {
            $this->entity_name = $entity_name;
        }
        if (!empty($entity_classname)) {
            $this->entity_classname = $entity_classname;
        }
        if (!empty($gds_entity_name)) {
            $this->gds_entity_name = $gds_entity_name;
        }
        parent::__construct($dataStoreService, $this->entity_name, $this->entity_classname, $this->gds_entity_name);
    }

    public function setConnection($dummy)
    {
        // No connection used here
    }

}