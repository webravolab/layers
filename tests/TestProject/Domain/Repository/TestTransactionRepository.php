<?php
namespace tests\TestProject\Domain\Repository;

use tests\TestProject\Domain\AggregateRoot\TestTransaction;
use tests\TestProject\Domain\Entity\TestEntity;
use Webravo\Common\Entity\AbstractEntity;
use Webravo\Common\Entity\EntityInterface;
use Webravo\Infrastructure\Library\DependencyBuilder;
use tests\TestProject\Infrastructure\Repository\TestStoreInterface;
use Webravo\Common\Contracts\RepositoryInterface;
use tests\TestProject\Infrastructure\Repository\TestTransactionStoreInterface;

class TestTransactionRepository implements RepositoryInterface
{
    // REPOSITORY USE STORE TO ACCESS DATA
    // REPOSITORY RECEIVE/RETURN ENTITY FROM/TO SERVICE
    // REPOSITORY RECEIVE/SEND DATA ARRAY FROM/TO STORE
    // REPOSITORY USE ENTITY toArray() and fromArray() TO CONVERT ENTITY <-> ARRAY

    protected $store;

    public function __construct(TestTransactionStoreInterface $store = null)
    {
        if (is_null($store)) {
            $this->store = DependencyBuilder::resolve('tests\TestProject\Infrastructure\Repository\TestStoreInterface');
        }
        else {
            $this->store = $store;
        }
    }

    public function getByGuid(string $guid): ?EntityInterface
    {
        $a_properties = $this->store->getByGuid($guid);
        if (is_null($a_properties) || !is_array($a_properties)) {
            return null;
        }
        $entity = TestTransaction::buildFromArray($a_properties);
        return $entity;
    }

    public function persist(EntityInterface $entity)
    {
        $this->store->persistEntity($entity);
        /*
        $a_data = $entity->toArray();
        $this->store->append($a_data);
        */
    }

    public function update(EntityInterface $entity)
    {
        $a_data = $entity->toArray();
        $this->store->update($a_data);
    }

    public function delete(EntityInterface $entity)
    {
        $a_data = $entity->toArray();
        $this->store->delete($a_data);
    }

    public function deleteByGuid(string $guid)
    {
        $this->store->deleteByGuid($guid);
    }


}