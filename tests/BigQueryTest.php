<?php

use tests\TestProject\Domain\Entity\TestEntity;
use tests\TestProject\Persistence\DataStore\TestDataStoreTable;
use tests\TestProject\Persistence\Hydrator\TestHydrator;
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
            $dataset = $client->createDataset($dataset_id);
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

        $client = new BigQueryService();

        $dataset_id = 'test_dataset';
        $table_id = 'test_table';

        $dataset = $client->getDataset($dataset_id);

        if (is_null($dataset)) {
            $dataset = $client->createDataset($dataset_id);
        }

        $a_tables = $client->listTables($dataset_id);

        $exists = false;
        foreach($a_tables as $table) {
            if ($table['tableId'] == $table_id) {
                $client->deleteTable($dataset_id, $table_id);
                $exists = true;
            }
        }

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

        self::assertEquals($table->id(),$table_id, "Table creation failed!");
    }

}