<?php
namespace tests\TestProject\Persistence\Eloquent\Store;

use tests\TestProject\Infrastructure\Repository\TestStoreInterface;
use tests\TestProject\Persistence\Hydrator\TestHydrator;
use Webravo\Infrastructure\Repository\HydratorInterface;
use tests\TestProject\Persistence\Eloquent\Model\TestEntityModel;

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

    public function getByGuidId(string $guid)
    {
        $o_entity = TestEntityModel::where('guid', $guid)->first();
        if (!$o_entity) {
            //Not found
            return null;
        }
        $a_properties = $this->hydrator->hydrate($o_entity);
        return $a_properties;
    }

    public function Append(array $a_properties)
    {
        $a_attributes = $this->hydrator->map($a_properties);
        TestEntityModel::create($a_attributes);
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