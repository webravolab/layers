<?php
namespace test\TestProject\Domain\Repository;

use Webravo\Common\Entity\EntityInterface;
use Webravo\Infrastructure\Library\DependencyBuilder;

class TestRepository implements TestRepositoryInterface
{
    // REPOSITORY USE STORE TO ACCESS DATA
    // REPOSITORY RECEIVE/RETURN ENTITY FROM/TO SERVICE
    // REPOSITORY RECEIVE/SEND DATA ARRAY FROM/TO STORE
    // REPOSITORY USE ENTITY toArray() and fromArray() TO CONVERT ENTITY <-> ARRAY

    protected $store;

    public function __construct(?test\TestProject\Infrastructure\Repository\TestStoreInterface $store)
    {
        if (is_null($store)) {
            $this->store = DependencyBuilder::resolve('test\TestProject\Infrastructure\Repository\TestStoreInterface');
        }
        else {
            $this->store = $store;
        }
    }

    public function getByGuid($guid)
    {
        // TODO: Implement getByGuid() method.
    }

    public function persist(EntityInterface $object)
    {
        // TODO: Implement persist() method.
    }
}
