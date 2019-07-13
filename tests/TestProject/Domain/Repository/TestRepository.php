<?php
namespace tests\TestProject\Domain\Repository;

use tests\TestProject\Domain\Entity\TestEntity;
use Webravo\Common\Entity\AbstractEntity;
use Webravo\Common\Entity\EntityInterface;
use Webravo\Infrastructure\Library\DependencyBuilder;
use tests\TestProject\Infrastructure\Repository\TestStoreInterface;

class TestRepository implements TestRepositoryInterface
{
    // REPOSITORY USE STORE TO ACCESS DATA
    // REPOSITORY RECEIVE/RETURN ENTITY FROM/TO SERVICE
    // REPOSITORY RECEIVE/SEND DATA ARRAY FROM/TO STORE
    // REPOSITORY USE ENTITY toArray() and fromArray() TO CONVERT ENTITY <-> ARRAY

    protected $store;

    public function __construct(?TestStoreInterface $store)
    {
        if (is_null($store)) {
            $this->store = DependencyBuilder::resolve('tests\TestProject\Infrastructure\Repository\TestStoreInterface');
        }
        else {
            $this->store = $store;
        }
    }

    public function getByGuid($guid): ?EntityInterface
    {
        $a_properties = $this->store->getByGuid($guid);
        $entity = TestEntity::buildFromArray($a_properties);
        return $entity;
    }

    public function persist(EntityInterface $object)
    {
        $a_data = $object->toArray();
        $this->store->Append($a_data);
    }

    public function update(EntityInterface $entity)
    {
        // TODO: Implement update() method.
    }

    public function delete(EntityInterface $entity)
    {
        // TODO: Implement delete() method.
    }


}
