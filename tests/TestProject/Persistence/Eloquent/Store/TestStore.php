<?php
namespace tests\TestProject\Persistence\Eloquent\Store;

use tests\TestProject\Infrastructure\Repository\TestStoreInterface;
use tests\TestProject\Persistence\Hydrator\TestHydrator;
use Webravo\Common\Contracts\HydratorInterface;
use tests\TestProject\Persistence\Eloquent\Model\TestEntityModel;
use Webravo\Persistence\Eloquent\DataTable\AbstractEloquentStore;

class TestStore extends AbstractEloquentStore implements TestStoreInterface
{

    public function __construct(HydratorInterface $hydrator = null)
    {
        if (is_null($hydrator)) {
            $hydrator = new TestHydrator();
        }
        parent::__construct($hydrator);
    }

    public function setConnection($db_connection_name)
    {
        // TODO: Implement setConnection() method.
    }

    public function getById($id)
    {
        // TODO: Implement getById() method.
    }

    public function getByGuid(string $guid)
    {
        $o_entity = $this->getObjectByGuid($guid);
        if (!$o_entity) {
            //Not found
            return null;
        }
        $a_properties = $this->hydrator->hydrate($o_entity);
        return $a_properties;
    }

    public function getObjectByGuid(string $guid)
    {
        return TestEntityModel::where('guid', $guid)->first();
    }


    public function append(array $a_properties)
    {
        $a_attributes = $this->hydrator->map($a_properties);
        TestEntityModel::create($a_attributes);
    }

    public function update(array $a_properties)
    {
        // TODO: Implement update() method.
    }

    public function delete(array $a_properties)
    {
        // TODO: Implement Delete() method.
    }

    public function deleteByGuid(string $guid)
    {
        // TODO: Implement deleteByGuid() method.
    }
}