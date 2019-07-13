<?php
namespace tests\TestProject\Persistence\Eloquent\Store;

use tests\TestProject\Infrastructure\Repository\TestStoreInterface;
use tests\TestProject\Persistence\Hydrator\TestHydrator;
use Webravo\Common\Contracts\HydratorInterface;
use tests\TestProject\Persistence\Eloquent\Model\TestEntityModel;
use Webravo\Persistence\Eloquent\DataTable\AbstractEloquentStore;

use Exception;
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
        $a_properties = $this->hydrator->hydrateEloquent($o_entity);
        return $a_properties;
    }

    public function getObjectByGuid(string $guid)
    {
        return TestEntityModel::where('guid', $guid)->first();
    }


    public function append(array $a_properties)
    {
        $a_attributes = $this->hydrator->mapEloquent($a_properties);
        if (!isset($a_properties['guid']) || empty($a_properties['guid'])) {
            throw new Exception('[TestStore][append] empty guid');
        }
        TestEntityModel::create($a_attributes);
    }

    public function update(array $a_properties)
    {
        if (!isset($a_properties['guid']) || empty($a_properties['guid'])) {
            throw new Exception('[TestStore][update] empty guid');
        }
        $a_attributes = $this->hydrator->mapEloquent($a_properties);
        $guid = $a_attributes['guid'];        // TODO: Implement update() method.
        $object = $this->getObjectByGuid($guid);
        if (!$object) {
            throw new Exception('[TestStore][update] guid ' . $guid . " not found");
        }
        $object->update($a_attributes);
    }

    public function delete(array $a_properties)
    {
        if (!isset($a_properties['guid']) || empty($a_properties['guid'])) {
            throw new Exception('[TestStore][delete] empty guid');
        }
        $guid = $a_properties['guid'];
        $this->deleteByGuid($guid);
    }

    public function deleteByGuid(string $guid)
    {
        TestEntityModel::where('guid', $guid)->delete();
    }
}