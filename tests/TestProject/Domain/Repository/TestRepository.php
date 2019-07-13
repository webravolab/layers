<?php
namespace tests\TestProject\Domain\Repository;

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

    public function getByGuid($guid)
    {
        $a_data = $this->store->getByGuidId($guid);
        return $a_data;
    }

    public function persist(EntityInterface $object)
    {
        $a_data = $object->toArray();
        $this->store->Append($a_data);
    }
}
