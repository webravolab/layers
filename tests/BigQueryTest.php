<?php

use tests\TestProject\Domain\Entity\TestEntity;
use tests\TestProject\Persistence\DataStore\TestDataStoreTable;
use tests\TestProject\Persistence\Hydrator\TestHydrator;
use Webpatser\Uuid\Uuid;
use Webravo\Infrastructure\Library\Configuration;
use Webravo\Common\ValueObject\DateTimeObject;
use Webravo\Persistence\Service\BigQueryService;
use Faker\Factory;

class BigQueryTest extends TestCase
{
    /*
    public function testBigQueryDataset()
    {
        $googleConfigFile = Configuration::get('GOOGLE_APPLICATION_CREDENTIALS');
        self::assertTrue(file_exists($googleConfigFile), "Google Credential file $googleConfigFile does not exists");

        $client = new BigQueryService();

        $a_datasets = $client->listDatasets();

        $dataset_id = 'test_dataset';

        $dataset = $client->getDataset($dataset_id);

        if (is_null($dataset)) {
            $options = [ 'location' => 'us'];
            $dataset = $client->createDataset($dataset_id, $options);
        }

        $retrieved_dataset = $client->getDataset($dataset_id);

        $id = $dataset->identity()['datasetId'];
        $retrieved_id = $retrieved_dataset->identity()['datasetId'];

        self::assertEquals($id, $retrieved_id, "Dataset Ids are different");


        $client->deleteDataset($dataset_id);

        $retrieved_dataset = $client->getDataset($dataset_id);

        self::assertNull($retrieved_dataset, "Dataset should be deleted");

        $dataset_id = 'unknown';

        $retrieved_dataset = $client->getDataset($dataset_id);

        self::assertNull($retrieved_dataset, "Dataset should not exists");

    }
*/

    public function testBigQueryTable()
    {

        $googleConfigFile = Configuration::get('GOOGLE_APPLICATION_CREDENTIALS');
        self::assertTrue(file_exists($googleConfigFile), "Google Credential file $googleConfigFile does not exists");

        $faker = Factory::create();

        $client = new BigQueryService();

        $dataset_id = 'test_dataset';
        $table_id = 'test_table';

        $dataset = $client->getDataset($dataset_id);

        if (is_null($dataset)) {
            $dataset = $client->createDataset($dataset_id);
        }

        $a_tables = $client->listTables($dataset_id);

        $exists = false;
        foreach($a_tables as $a_table) {
            if ($a_table['tableId'] == $table_id) {
                $exists = true;
            }
        }

        if (!$exists) {
            $fields = [
                [
                    'name' => 'guid',
                    'type' => 'string',
                    'mode' => 'required',
                ],
                [
                    'name' => 'name',
                    'type' => 'string',
                    'mode' => 'nullable',
                ],
                [
                    'name' => 'fk_id',
                    'type' => 'integer',
                    'mode' => 'nullable',
                ],
                [
                    'name' => 'created_at',
                    'type' => 'datetime',
                ],

            ];
            $schema = ['fields' => $fields];

            $table = $client->createTable($dataset_id, $table_id, $schema);

            self::assertEquals($table->id(), $table_id, "Table creation failed!");
        }
        else {
            $table = $client->getTable($dataset_id, $table_id);
        }


        $guid =  $guid = (string) Uuid::generate();
        $name = $faker->name();
        $fk = $faker->numberBetween(1000,100000);
        $created_at = $faker->dateTimeThisYear();
        $created_at = $created_at->format('Y-m-d H:i:s') . '.' . $faker->numberBetween(100000,999999);
        $created_at = new DateTime($created_at);

        $a_data = [
            'guid' => $guid,
            'name' => $name,
            'fk_id' => $fk,
            'created_at' => $created_at
        ];

        $client->insertRow($table, $a_data);

        $a_data = [];
        for ($x=0; $x<10; $x++) {
            $guid =  $guid = (string) Uuid::generate();
            $name = $faker->name();
            $fk = $faker->numberBetween(1000,100000);
            $created_at = $faker->dateTimeThisYear();
            $created_at = $created_at->format('Y-m-d H:i:s') . '.' . $faker->numberBetween(100000,999999);
            $created_at = new DateTime($created_at);

            $a_data[] = [
                'data' => [
                    'guid' => $guid,
                    'name' => $name,
                    'fk_id' => $fk,
                    'created_at' => $created_at
                ]
            ];
        }
        $transaction_id = $faker->numberBetween(1000,100000);
        $client->insertRows($table, $a_data, $transaction_id);
    }

}