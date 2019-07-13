<?php
namespace tests\TestProject\Infrastructure\Repository;

interface TestStoreInterface {

    public function setConnection($db_connection_name);

    public function getById($id);

    public function getByGuidId(string $id);

    public function Append(array $data);

    public function Update($id, array $data);

    public function Delete($id);

}