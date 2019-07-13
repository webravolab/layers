<?php
namespace tests\TestProject\Persistence\Eloquent\Store;

use test\TestProject\Infrastructure\Repository\TestStoreInterface;
use tests\TestProject\Persistence\Hydrator\TestHydrator;
use Webravo\Infrastructure\Repository\HydratorInterface;

class TestStore implements TestStoreInterface
{
    protected $hydrator;

    public function __construct(HydratorInterface $hydrator = null)
    {
        if (is_null($hydrator)) {
            $hydrator = new TestHydrator();
        }
        $this->hydrator = $hydrator;
    }

    public function setConnection($db_connection_name)
    {
        // TODO: Implement setConnection() method.
    }

    public function getById($id)
    {
        // TODO: Implement getById() method.
    }

    public function Append(array $data)
    {
        // TODO: Implement Append() method.
    }

    public function Update($id, array $data)
    {
        // TODO: Implement Update() method.
    }

    public function Delete($id)
    {
        // TODO: Implement Delete() method.
    }
}