<?php
use Webravo\Infrastructure\Library\Configuration;

use Faker\Factory;
use Tests\Entity\TestEntity;

class DataStoreTest extends TestCase
{
    public function testDataStore()
    {
        $googleProjectId = Configuration::get('GOOGLE_PROJECT_ID');
        $googleConfigFile = Configuration::get('GOOGLE_APPLICATION_CREDENTIALS');

        /*
        $dataStoreClient = new \Google\Cloud\Datastore\DatastoreClient([
            'projectId' => $googleProjectId,
            'keyFilePath' => $googleConfigFile,
        ]);
        */

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
        $a->setCreatedAt($created_at);

        $dtOne = new TestDataStoreTable($dataStoreClient, null);

        // Save entity
        $dtOne->persist($a);

        // retrieve entity
        $guid = $a->getGuid();

        $b = $dtOne->getByGuid($guid);

        self::assertEquals($a->getName(), $b->getName(), 'Entity name saved and read does not match');

        // Update entity
        $b->setName('Giorgio Bianchi');

        $dtOne->update($b);

        $c = $dtOne->getByGuid($guid);

        self::assertEquals($b->getName(), $c->getName(), 'Entity name after update does not match');

        $dtOne->delete($c);

        // Double deletion .. no errors
        $dtOne->delete($c);

        // Massive test
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
            $a->setCreatedAt($created_at);
            $dtOne->persist($a);
        }

        $end_time = microtime(true);

        echo "$x entities saved in " . ($end_time - $start_time) . " seconds";

    }
}

Class TestDataStoreTable extends \Webravo\Persistence\Repository\AbstractDataStoreTable
{
    protected $entity_name = 'TestEntity';
    protected $entity_classname = 'Tests\Entity\TestEntity';
}