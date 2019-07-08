<?php
namespace tests\DataStoreTable;

use Webravo\Persistence\Repository\AbstractDataStoreTable;

Class TestDataStoreTable extends AbstractDataStoreTable
{
    protected $entity_name = 'TestEntity';
    protected $entity_classname = 'Tests\Entity\TestEntity';
}