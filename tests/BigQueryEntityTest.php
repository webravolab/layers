<?php

use tests\TestProject\Domain\Entity\TestEntity;
use tests\TestProject\Persistence\BigQuery\TestBigQueryTable;
use tests\TestProject\Persistence\Hydrator\TestHydrator;
// use Webpatser\Uuid\Uuid;
use Webravo\Infrastructure\Library\Configuration;
use Webravo\Common\ValueObject\DateTimeObject;
use Webravo\Persistence\Service\BigQueryService;
use tests\TestProject\Domain\Repository\TestRepository;
use tests\TestProject\Domain\Service\TestService;
use Faker\Factory;

class BigQueryEntityTest extends TestCase
{
    public function testBigQueryDataset()
    {
        $faker = Factory::create();
        $name = $faker->name();
        $fk = $faker->numberBetween(1000, 100000);
        $created_at = $faker->dateTimeThisYear();
        $created_at = $created_at->format('Y-m-d H:i:s') . '.' . $faker->numberBetween(100000, 999999);
        $created_at = new DateTimeObject($created_at);

        $entity = new TestEntity();
        $entity_name = get_class($entity);
        $entity->setName($name);
        $entity->setForeignKey($fk);
        $entity->setCreatedAt($created_at);

        $guid = $entity->getGuid();

        $bigQueryClient = new BigQueryService();
        $store = new TestBigQueryTable($bigQueryClient);
        $repository = new TestRepository($store);
        $service = new TestService($repository);

        $service->create($entity);

        $retrieved_entity = $service->getByGuid($guid);

        $this->assertEquals($entity->getGuid(), $retrieved_entity->getGuid());
        $this->assertEquals($entity->getName(), $retrieved_entity->getName());
        $this->assertEquals($entity->getForeignKey(), $retrieved_entity->getForeignKey());
        $this->assertEquals($entity->getCreatedAt()->toISOString(), $retrieved_entity->getCreatedAt()->toISOString());
    }
}