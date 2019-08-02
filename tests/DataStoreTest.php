<?php

use tests\TestProject\Domain\Entity\TestEntity;
use tests\TestProject\Persistence\DataStore\TestDataStoreTable;
use tests\TestProject\Persistence\Hydrator\TestHydrator;
use Webravo\Infrastructure\Library\Configuration;
use Webravo\Common\ValueObject\DateTimeObject;
use Webravo\Persistence\Service\DataStoreService;
use Faker\Factory;

class DataStoreTest extends TestCase
{
    public function testDataStore()
    {

        $googleConfigFile = Configuration::get('GOOGLE_APPLICATION_CREDENTIALS');
        self::assertTrue(file_exists($googleConfigFile), "Google Credential file $googleConfigFile does not exists");

        $dataStoreClient = new DataStoreService();

        $faker = Factory::create();
        $name = $faker->name();
        $fk = $faker->numberBetween(1000,100000);
        $created_at = $faker->dateTimeThisYear();
        $created_at = $created_at->format('Y-m-d H:i:s') . '.' . $faker->numberBetween(100000,999999);
        $created_at = new DateTime($created_at);

        $a = new TestEntity();
        $entity_name = get_class($a);
        $a->setName($name);
        $a->setForeignKey($fk);
        $a->setCreatedAt(new DateTimeObject($created_at));

        $hydrator = new TestHydrator();

        $dtOne = new TestDataStoreTable($dataStoreClient, $hydrator);

        // Save entity
        $a_properties = $a->toArray();
        $dtOne->append($a_properties);

        // retrieve entity
        $guid = $a->getGuid();

        $b = TestEntity::buildFromArray($dtOne->getByGuid($guid));

        self::assertEquals($a->getName(), $b->getName(), 'Entity name saved and read does not match');

        // Update entity
        $b->setName('Giorgio Bianchi');

        $a_properties = $b->toArray();
        $dtOne->update($a_properties);

        $c = TestEntity::buildFromArray($dtOne->getByGuid($guid));

        self::assertEquals($b->getName(), $c->getName(), 'Entity name after update does not match');

        $a_properties = $c->toArray();
        $dtOne->delete($a_properties);

        // Double deletion .. no errors
        $a_properties = $c->toArray();
        $dtOne->delete($a_properties);
    }

    public function testDataStoreMassiveInsert()
    {
        $googleConfigFile = Configuration::get('GOOGLE_APPLICATION_CREDENTIALS');
        self::assertTrue(file_exists($googleConfigFile), "Google Credential file $googleConfigFile does not exists");

        $dataStoreClient = new DataStoreService();
        $dtOne = new TestDataStoreTable($dataStoreClient, null);

        $faker = Factory::create();

        // Massive insert of 10 records
        $start_time = microtime(true);

        for($x=0; $x<10; $x++) {
            $name = $faker->name();
            $fk = $faker->numberBetween(1000,100000);
            $created_at = $faker->dateTimeThisYear();
            $created_at = $created_at->format('Y-m-d H:i:s') . '.' . $faker->numberBetween(100000,999999);
            $created_at = new DateTime($created_at);
            $a = new TestEntity();
            $a->setName($name);
            $a->setForeignKey($fk);
            $a->setCreatedAt(new DateTimeObject($created_at));

            // Save entity
            $a_properties = $a->toArray();
            $dtOne->append($a_properties);
        }

        $end_time = microtime(true);

        echo "$x entities saved in " . ($end_time - $start_time) . " seconds";
    }

    public function testDataStoreCursor()
    {
        $googleProjectId = Configuration::get('GOOGLE_PROJECT_ID');
        $googleConfigFile = Configuration::get('GOOGLE_APPLICATION_CREDENTIALS');

        $faker = Factory::create();

        $dataStoreClient = new \Webravo\Persistence\Service\DataStoreService();

        $dtOne = new TestDataStoreTable($dataStoreClient, null);

        $cursor = '';
        $test_entities = [];

        $created_at = $faker->dateTimeThisYear();
        $page_size = $faker->numberBetween(5,150);

        while (true) {
            $results = $dtOne->paginateByKey('created_at', '>', $created_at, 'asc', $page_size, $cursor);
            $test_entities = array_merge(array_values($test_entities), array_values($results['entities']));
            $cursor = $results['page_cursor'];
            if (empty($cursor)) {
                break;
            }
        }
        $previous_entity = [];
        foreach($test_entities as $idx => $entity) {
            if ($idx > 0) {
                self::assertTrue($previous_entity['created_at'] < $entity['created_at'], 'Bad Dates order');
            }
            $previous_entity = $entity;
        }

        self::assertTrue(count($test_entities) > 0, 'Entities not found...');


        $cursor = '';
        $test_entities = [];

        $fk = $faker->numberBetween(1000,10000);
        $page_size = $faker->numberBetween(5,20);

        while (true) {
            $results = $dtOne->paginateByKey('fk_id', '>=', $fk, 'desc', $page_size, $cursor);
            $test_entities = array_merge(array_values($test_entities), array_values($results['entities']));
            $cursor = $results['page_cursor'];
            if (empty($cursor)) {
                break;
            }
        }
        $previous_entity = [];
        foreach($test_entities as $idx => $entity) {
            if ($idx > 0) {
                self::assertTrue($previous_entity['fk_id'] >= $entity['fk_id'], 'Bad fk order');
            }
            $previous_entity = $entity;
        }

        self::assertTrue(count($test_entities) > 0, 'Entities not found...');
    }

    public function testDataStoreGetAll()
    {
        $googleProjectId = Configuration::get('GOOGLE_PROJECT_ID');
        $googleConfigFile = Configuration::get('GOOGLE_APPLICATION_CREDENTIALS');

        $faker = Factory::create();

        $dataStoreClient = new \Webravo\Persistence\Service\DataStoreService();

        $dtOne = new TestDataStoreTable($dataStoreClient, null);

        $attempt = 0;

        $fk = $faker->numberBetween(1000,100000);

        $results = $dtOne->paginateByKey('fk_id', '>', $fk, 'asc', 10);
        self::assertTrue(count($results) > 0, 'Bad results...');
        $entities = $results['entities'];
        self::assertTrue(count($entities) > 0, 'Entities not found...');

        $fk = $entities[0]['fk_id'];

        $results = $dtOne->getAllByKey('fk_id', $fk);
        self::assertEquals($results[0]['fk_id'], $fk);
    }


}

