<?php

use tests\TestProject\Domain\Entity\TestEntity;
use tests\TestProject\Persistence\DataStore\TestDataStoreTable;
use tests\TestProject\Persistence\Hydrator\TestHydrator;
use Webravo\Infrastructure\Library\Configuration;
use Webravo\Common\ValueObject\DateTimeObject;
use Faker\Factory;
use DateTime;

class DataStoreTest extends TestCase
{
    public function testDataStore()
    {

        $googleConfigFile = Configuration::get('GOOGLE_APPLICATION_CREDENTIALS');
        self::assertTrue(file_exists($googleConfigFile), "Google Credential file $googleConfigFile does not exists");

        $dataStoreClient = new \Webravo\Persistence\Service\DataStoreService();

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

        $dataStoreClient = new \Webravo\Persistence\Service\DataStoreService();
        $dtOne = new TestDataStoreTable($dataStoreClient, null);

        $faker = Factory::create();

        // Massive insert of 100 records
        $start_time = microtime(true);

        for($x=0; $x<100; $x++) {
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

    // TODO
    /*
    public function testDataStoreCursor()
    {
        $googleProjectId = Configuration::get('GOOGLE_PROJECT_ID');
        $googleConfigFile = Configuration::get('GOOGLE_APPLICATION_CREDENTIALS');

        $dataStoreClient = new \Webravo\Persistence\Service\DataStoreService();

        $dtOne = new TestDataStoreTable($dataStoreClient, null);

        self::assertTrue(false, 'Test incomplete...');
   }
   */
}

