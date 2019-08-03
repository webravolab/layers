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


        $a_tables = $client->listTables($dataset_id);

        foreach($a_tables as $a_table) {
            $client->deleteTable($dataset_id,$a_table['tableId']);
        }

        $client->deleteDataset($dataset_id);

        $retrieved_dataset = $client->getDataset($dataset_id);

        self::assertNull($retrieved_dataset, "Dataset should be deleted");

        $dataset_id = 'unknown';

        $retrieved_dataset = $client->getDataset($dataset_id);

        self::assertNull($retrieved_dataset, "Dataset should not exists");

    }

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
        for ($x=0; $x<150; $x++) {
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


        $a_data = [
            'guid' => $guid,
            'name' => $name,
            'fk_id' => $fk,
            'created_at' => 'bad date'
        ];

        self::expectExceptionMessage('[BigQueryService][insertRow][test_table]: Invalid datetime string "bad date"');
        $client->insertRow($table, $a_data);
    }


    public function testBigQueryTablePaginate()
    {
        $googleConfigFile = Configuration::get('GOOGLE_APPLICATION_CREDENTIALS');
        self::assertTrue(file_exists($googleConfigFile), "Google Credential file $googleConfigFile does not exists");

        $faker = Factory::create();

        $client = new BigQueryService();

        $dataset_id = 'test_dataset';
        $table_id = 'test_table';
        $dataset = $client->getDataset($dataset_id);
        self::assertNotNull($dataset);
        $table = $client->getTable($dataset_id, $table_id);
        self::assertNotNull($table);

        $page_size = $faker->numberBetween(5,15);
        $cursor = '';
        $test_entities = [];

        while (true) {
            $results = $client->PaginateRows($table, $page_size, $cursor);
            $test_entities = array_merge(array_values($test_entities), array_values($results['entities']));
            $cursor = $results['page_cursor'];
            if (empty($cursor)) {
                break;
            }
        }

        self::assertTrue(count($test_entities) > 0, "No rows found");

    }

    public function testBigQueryTableGetByKey()
    {
        $googleConfigFile = Configuration::get('GOOGLE_APPLICATION_CREDENTIALS');
        self::assertTrue(file_exists($googleConfigFile), "Google Credential file $googleConfigFile does not exists");

        $faker = Factory::create();

        $client = new BigQueryService();

        $dataset_id = 'test_dataset';
        $table_id = 'test_table';
        $dataset = $client->getDataset($dataset_id);
        self::assertNotNull($dataset);
        $table = $client->getTable($dataset_id, $table_id);
        self::assertNotNull($table);

        $start = $faker->numberBetween(1,100);
        $results = $client->PaginateRows($table, 10, $start);

        self::assertTrue(count($results) > 0, "No rows found");

        $fk = $results['entities'][9]['fk_id'];

        $results = $client->getByKey($dataset_id, $table_id, 'fk_id', $fk);

        self::assertTrue(count($results) > 0, "No rows found");

        self::assertEquals($fk, $results[0]['fk_id'], "Error retrieving fk_id = $fk");
    }


    public function testBigQueryTablePaginateByKey()
    {
        $googleConfigFile = Configuration::get('GOOGLE_APPLICATION_CREDENTIALS');
        self::assertTrue(file_exists($googleConfigFile), "Google Credential file $googleConfigFile does not exists");

        $faker = Factory::create();

        $client = new BigQueryService();

        $dataset_id = 'test_dataset';
        $table_id = 'test_table';
        $dataset = $client->getDataset($dataset_id);
        self::assertNotNull($dataset);
        $table = $client->getTable($dataset_id, $table_id);
        self::assertNotNull($table);

        $page_size = $faker->numberBetween(5,15);
        $cursor = '';
        $test_entities = [];

        while (true) {
            $results = $client->PaginateByKey($dataset_id, $table_id, 'name', '>', 'Grassi', 'desc', $page_size, $cursor);
            $test_entities = array_merge(array_values($test_entities), array_values($results['entities']));
            $cursor = $results['page_cursor'];
            if (empty($cursor)) {
                break;
            }
        }

        self::assertTrue(count($test_entities) > 0, "No rows found");

    }

}