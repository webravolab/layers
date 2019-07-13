<?php
namespace tests\TestProject\Domain\DataStoreTable;

use Webravo\Persistence\Repository\AbstractDataStoreTable;

Class TestDataStoreTable extends AbstractDataStoreTable
{
    protected $entity_name = 'TestEntity';
    protected $entity_classname = 'tests\TestProject\Domain\Entity\TestEntity';
}