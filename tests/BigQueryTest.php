<?php
use Webpatser\Uuid\Uuid;
use Webravo\Infrastructure\Library\Configuration;
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

        $dataset_id = 'test_dummy_dataset';

        $dataset = $client->getDataset($dataset_id);

        if (is_null($dataset)) {
            $options = [ 'location' => 'us'];
            $dataset = $client->createDataset($dataset_id, $options);
        }

        $retrieved_dataset = $client->getDataset($dataset_id);

        $id = $dataset->id();
        $retrieved_id = $retrieved_dataset->id();

        self::assertEquals($id, $retrieved_id, "Dataset Ids are different");

        $a_tables = $client->listTables($dataset_id);

        foreach($a_tables as $a_table) {
            $client->deleteTable($dataset_id,$a_table['tableId']);
            echo "Table deleted" . PHP_EOL;
        }

        $client->deleteDataset($dataset_id);
        echo "Dataset deleted" . PHP_EOL;


        $retrieved_dataset = $client->getDataset($dataset_id);

        self::assertNull($retrieved_dataset, "Dataset should be deleted");

        $dataset_id = 'unknown';

        $retrieved_dataset = $client->getDataset($dataset_id);

        self::assertNull($retrieved_dataset, "Dataset should not exists");

    }

    public function testBigQueryRawQuery()
    {
        $googleConfigFile = Configuration::get('GOOGLE_APPLICATION_CREDENTIALS');
        self::assertTrue(file_exists($googleConfigFile), "Google Credential file $googleConfigFile does not exists");

        $faker = Factory::create();

        $client = new BigQueryService();

        $dataset_id = 'test_dataset';
        $table_id = 'test_table';

        $a_rows = $client->getRawQuery($dataset_id, $table_id, "
        SELECT * FROM `{$dataset_id}.{$table_id}`
        WHERE guid > '5' AND fk_id < 20000
        ORDER BY created_at DESC 
        LIMIT 10  
        ");

        self::assertEquals(count($a_rows), 10, 'Cannot retrieve 10 records by getRawQuery');

        $a_rows = $client->getRawQuery($dataset_id, $table_id, "
        SELECT guid FROM `{$dataset_id}.{$table_id}`
        WHERE guid > @guid AND fk_id < @fk
        ORDER BY created_at DESC 
        LIMIT @limit  
        ", [
            'guid' => '5',
            'fk' => 15000,
            'limit' => 5,
        ]);

        self::assertEquals(count($a_rows), 5, 'Cannot retrieve 5 records by getRawQuery');

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
            echo "Dataset created" . PHP_EOL;
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
        $table = $client->getTable($dataset_id, $table_id);

        $guid_single =  (string) Uuid::generate();
        $name = $faker->name();
        $fk = $faker->numberBetween(1000,100000);
        $created_at = $faker->dateTimeThisYear();
        $created_at = $created_at->format('Y-m-d H:i:s') . '.' . $faker->numberBetween(100000,999999);
        $created_at = new DateTime($created_at);

        $a_data = [
            'guid' => $guid_single,
            'name' => $name,
            'fk_id' => $fk,
            'created_at' => $created_at
        ];

        $client->insertRow($dataset_id, $table_id, $a_data);

        $a_data = [];
        for ($x=0; $x<150; $x++) {
            $guid =  (string) Uuid::generate();
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
        $client->insertRows($dataset_id, $table_id, $a_data, $transaction_id);

        // Test failed insert
        $guid =  (string) Uuid::generate();
        $a_data = [
            'guid' => $guid,
            'name' => $name,
            'fk_id' => $fk,
            'created_at' => 'bad date'
        ];

        self::expectExceptionMessage('[BigQueryService][insertRow][test_table]: Invalid datetime string "bad date"');
        $client->insertRow($dataset_id, $table_id, $a_data);
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
        $results = $client->paginateRows($dataset_id, $table_id, 10, $start);

        self::assertTrue(count($results['entities']) > 0, "No rows found");

        $fk = $results['entities'][9]['fk_id'];

        $results = $client->getByKey($dataset_id, $table_id, 'fk_id', $fk);

        self::assertTrue(count($results) > 0, "No rows found");

        self::assertEquals($fk, $results[0]['fk_id'], "Error retrieving fk_id = $fk");
    }

    public function testBigQueryUpdate()
    {
        $googleConfigFile = Configuration::get('GOOGLE_APPLICATION_CREDENTIALS');
        self::assertTrue(file_exists($googleConfigFile), "Google Credential file $googleConfigFile does not exists");

        $client = new BigQueryService();

        $dataset_id = 'test_dataset';
        $table_id = 'test_table';
        $dataset = $client->getDataset($dataset_id);
        self::assertNotNull($dataset);
        $table = $client->getTable($dataset_id, $table_id);
        self::assertNotNull($table);

        $results = $client->paginateRows($dataset_id, $table_id, 10, 0);

        self::assertTrue(count($results['entities']) > 0, "No rows found");


        $guid = $results['entities'][0]['guid'];

        // Test Update
        $a_data = [
            'guid' => $guid,
            'name' => 'Changed name',
            'fk_id' => 9999,
        ];

        $success = $client->updateRow($dataset_id, $table_id, $a_data);

        self::assertTrue($success, 'Update failed probably because updated record is into streaming buffer');

    }

    public function testBigQueryDelete()
    {
        $googleConfigFile = Configuration::get('GOOGLE_APPLICATION_CREDENTIALS');
        self::assertTrue(file_exists($googleConfigFile), "Google Credential file $googleConfigFile does not exists");

        $client = new BigQueryService();

        $dataset_id = 'test_dataset';
        $table_id = 'test_table';
        $dataset = $client->getDataset($dataset_id);
        self::assertNotNull($dataset);
        $table = $client->getTable($dataset_id, $table_id);
        self::assertNotNull($table);

        $results = $client->paginateRows($dataset_id, $table_id, 10, 0);

        self::assertTrue(count($results['entities']) > 0, "No rows found");

        $guid = $results['entities'][0]['guid'];

        $success = $client->deleteRow($dataset_id, $table_id, $guid, 'guid');

        self::assertTrue($success, 'Delete failed probably because deleted record is into streaming buffer');

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
            $results = $client->paginateRows($dataset_id, $table_id, $page_size, $cursor);
            $test_entities = array_merge(array_values($test_entities), array_values($results['entities']));
            $cursor = $results['page_cursor'];
            if (empty($cursor)) {
                break;
            }
        }

        self::assertTrue(count($test_entities) > 0, "No rows found");

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