<?php

use Faker\Factory;
use tests\TestProject\Domain\Entity\TestEntity;
use tests\TestProject\Persistence\Eloquent\Store\TestStore;
use tests\TestProject\Persistence\DataStore\TestDataStoreTable;
use tests\TestProject\Domain\Repository\TestRepository;
use tests\TestProject\Domain\Service\TestService;
use Webravo\Common\ValueObject\DateTimeObject;

class EntityTest extends TestCase
{

    public function testEntity2Eloquent()
    {
        $faker = Factory::create();
        $name = $faker->name();
        $fk = $faker->numberBetween(1000,100000);
        $created_at = $faker->dateTimeThisYear();
        $created_at = $created_at->format('Y-m-d H:i:s') . '.' . $faker->numberBetween(100000,999999);
        $created_at = new DateTimeObject($created_at);

        $entity = new TestEntity();
        $entity_name = get_class($entity);
        $entity->setName($name);
        $entity->setForeignKey($fk);
        $entity->setCreatedAt($created_at);

        $guid = $entity->getGuid();

        $store = new TestStore();
        $repository = new TestRepository($store);
        $service = new TestService($repository);

        $service->create($entity);

        $retrieved_entity = $service->getByGuid($guid);

        $this->assertEquals($entity->getGuid(), $retrieved_entity->getGuid());
        $this->assertEquals($entity->getName(), $retrieved_entity->getName());
        $this->assertEquals($entity->getForeignKey(), $retrieved_entity->getForeignKey());
        $this->assertEquals($entity->getCreatedAt()->toISOString(), $retrieved_entity->getCreatedAt()->toISOString());

        // Test Update
        $fk = $faker->numberBetween(1000,100000);
        $entity->setForeignKey($fk);
        $service->update($entity);
        $retrieved_entity = $service->getByGuid($guid);
        $this->assertEquals($entity->getForeignKey(), $retrieved_entity->getForeignKey());

        // Test Delete
        $service->delete($entity);
        $deleted_entity = $service->getByGuid($guid);
        $this->assertNull($deleted_entity);
    }

    public function testEntity2DataStore()
    {
        $faker = Factory::create();
        $name = $faker->name();
        $fk = $faker->numberBetween(1000,100000);
        $created_at = $faker->dateTimeThisYear();
        $created_at = $created_at->format('Y-m-d H:i:s') . '.' . $faker->numberBetween(100000,999999);
        $created_at = new DateTimeObject($created_at);

        $entity = new TestEntity();
        $entity_name = get_class($entity);
        $entity->setName($name);
        $entity->setForeignKey($fk);
        $entity->setCreatedAt($created_at);

        $guid = $entity->getGuid();

        $dataStoreClient = new \Webravo\Persistence\Service\DataStoreService();
        $store = new TestDataStoreTable($dataStoreClient);
        $repository = new TestRepository($store);
        $service = new TestService($repository);

        $service->create($entity);

        $retrieved_entity = $service->getByGuid($guid);

        $this->assertEquals($entity->getGuid(), $retrieved_entity->getGuid());
        $this->assertEquals($entity->getName(), $retrieved_entity->getName());
        $this->assertEquals($entity->getForeignKey(), $retrieved_entity->getForeignKey());
        $this->assertEquals($entity->getCreatedAt()->toISOString(), $retrieved_entity->getCreatedAt()->toISOString());

        // Test Update
        $fk = $faker->numberBetween(1000,100000);
        $entity->setForeignKey($fk);
        $service->update($entity);
        $retrieved_entity = $service->getByGuid($guid);
        $this->assertEquals($entity->getForeignKey(), $retrieved_entity->getForeignKey());

        // Test Delete
        $service->delete($entity);
        $deleted_entity = $service->getByGuid($guid);
        $this->assertNull($deleted_entity);

        // GQL TEST QUERY
        // select * from TestEntity where created_at > DATETIME("2019-01-01T00:00:00Z")
    }

}
