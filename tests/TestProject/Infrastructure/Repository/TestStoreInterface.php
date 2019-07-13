<?php
namespace tests\TestProject\Infrastructure\Repository;

use Webravo\Common\Contracts\StoreInterface;

interface TestStoreInterface extends StoreInterface
{
    // Add here any additional methods specific to the store
    public function setConnection($db_connection_name);

    /**
     * Methods inherited by StoreInterface:
     **/

    // public function append(array $data);

    // public function getByGuid($guid);

    // public function getObjectByGuid($guid);

    // public function update(array $data);

    // public function delete(array $data);

    // public function deleteByGuid($guid);

}