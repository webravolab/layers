<?php
namespace tests\TestProject\Persistence\DataSTore\TestDataStoreTable;

use Webravo\Persistence\Repository\AbstractDataStoreTable;

Class TestDataStoreTable extends AbstractDataStoreTable
{
    protected $entity_name = 'TestEntity';
    protected $entity_classname = 'tests\TestProject\Domain\Entity\TestEntity';
}