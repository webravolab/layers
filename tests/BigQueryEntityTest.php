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
    public function testBigQueryEntity()
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

        // Massive insert
        $fk = 9999;
        for ($x=0; $x<5; $x++) {
            $name = $faker->name();
            $created_at = $faker->dateTimeThisYear();
            $created_at = $created_at->format('Y-m-d H:i:s') . '.' . $faker->numberBetween(100000, 999999);
            $created_at = new DateTimeObject($created_at);

            $entity = new TestEntity();
            $entity->setName($name);
            $entity->setForeignKey($fk);
            $entity->setCreatedAt($created_at);
            $service->create($entity);
        }

        // Test getAllByKey
        $a_rows = $store->getAllByKey('fk_id', 9999);

        self::assertTrue(count($a_rows) >= 5, "Failed to retrieved rows by key using getAllByKey");

        // Test Paginate
        $page_size = $faker->numberBetween(5,15);
        $cursor = '';
        $test_entities = [];

        while (true) {
            $results = $store->paginateByKey( 'name', '>', 'Grassi', 'desc', $page_size, $cursor);
            $test_entities = array_merge(array_values($test_entities), array_values($results['entities']));
            $cursor = $results['page_cursor'];
            if (empty($cursor)) {
                break;
            }
        }

        self::assertTrue(count($test_entities) > 0, "No rows found");

        foreach($test_entities as $idx => $entity) {
            self::assertTrue($entity['name'] > 'Grassi');
            if ($idx > 0) {
                self::assertTrue($entity['name'] < $test_entities[$idx-1]['name']);
            }
        }
    }

    public function testBigQueryEntityUpdate()
    {
        $faker = Factory::create();

        $bigQueryClient = new BigQueryService();
        $store = new TestBigQueryTable($bigQueryClient);
        $repository = new TestRepository($store);
        $service = new TestService($repository);

        $results = $store->paginateByKey( 'name', '>', 'A', 'asc', 10, '');
        $a_row = $results['entities'][0];
        $guid = $a_row['guid'];

        // Test Update
        // THIS TEST COULD FAIL IF UPDATE AFFECTS A RECORD THAT IS STILL IN STREAMING BUFFER
        $entity = $service->getByGuid($guid);
        $fk = $faker->numberBetween(1000,100000);
        $entity->setForeignKey($fk);
        $service->update($entity);
        $retrieved_entity = $service->getByGuid($guid);
        $this->assertEquals($entity->getForeignKey(), $retrieved_entity->getForeignKey(), "Failed update test");

    }

    public function testBigQueryEntityDelete()
    {
        $faker = Factory::create();

        $bigQueryClient = new BigQueryService();
        $store = new TestBigQueryTable($bigQueryClient);
        $repository = new TestRepository($store);
        $service = new TestService($repository);

        $results = $store->paginateByKey( 'name', '>', 'A', 'asc', 10, '');
        $a_row = $results['entities'][0];
        $guid = $a_row['guid'];

        // Test Delete
        // THIS TEST COULD FAIL IF DELETE AFFECTS A RECORD THAT IS STILL IN STREAMING BUFFER
        $entity = $service->getByGuid($guid);
        $service->delete($entity);
        $deleted_entity = $service->getByGuid($guid);
        $this->assertNull($deleted_entity, "Failed delete test");
    }


}