<?php
namespace tests\TestProject\Persistence\BigQuery;

use tests\TestProject\Persistence\Hydrator\TestHydrator;
use Webravo\Common\Contracts\HydratorInterface;
use Webravo\Infrastructure\Library\DependencyBuilder;
use Webravo\Infrastructure\Service\BigQueryServiceInterface;
use Webravo\Persistence\BigQuery\DataTable\AbstractBigQueryStore;
use tests\TestProject\Infrastructure\Repository\TestStoreInterface;

Class TestBigQueryTable extends AbstractBigQueryStore implements TestStoreInterface
{
    protected $entity_name = 'TestEntity';
    protected $entity_classname = 'tests\TestProject\Domain\Entity\TestEntity';
    protected $bg_entity_name = "TestEntity";
    protected $bg_dataset_name = 'TestDataset';

    public function __construct(BigQueryServiceInterface $bigQueryService, HydratorInterface $hydrator = null, $entity_name = null, $entity_classname = null, $bg_entity_name = null, $bg_dataset_name= null)
    {
        if (is_null($bigQueryService)) {
            $bigQueryService = DependencyBuilder::resolve('Webravo\Infrastructure\Service\BigQueryServiceInterface');
        }
        if (is_null($hydrator)) {
            $hydrator = new TestHydrator();
        }
        if (!empty($entity_name)) {
            $this->entity_name = $entity_name;
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
        parent::__construct($bigQueryService, $hydrator, $this->entity_name, $this->entity_classname, $this->bg_entity_name, $this->bg_dataset_name);
    }

    public function setConnection($dummy)
    {
        // No connection used here
    }

}