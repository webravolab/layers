<?php

namespace Webravo\Persistence\Service;

use Webravo\Infrastructure\Library\Configuration;
use Webravo\Infrastructure\Service\BigQueryServiceInterface;
use Google\Cloud\BigQuery\BigQueryClient;

class BigQueryService implements BigQueryServiceInterface {

    protected $bigQueryClient = null;

    public function __construct()
    {
        $googleProjectId = Configuration::get('GOOGLE_PROJECT_ID');
        $googleConfigFile = Configuration::get('GOOGLE_APPLICATION_CREDENTIALS');

        $this->setConnection(new BigQueryClient([
            'projectId' => $googleProjectId,
            'keyFilePath' => $googleConfigFile,
            ])
        );
    }

    /**
     * Inject the connection from external
     * @param $connection
     * @return mixed
     */
    public function setConnection($connection)
    {
        $this->bigQueryClient = $connection;
    }

    /**
     * Get the current connection
     * @return mixed
     */
    public function getConnection()
    {
        return $this->bigQueryClient;
    }

    public function createDataset($dataset_id)
    {
        $dataset = $this->bigQueryClient->createDataset($dataset_id);
        return $dataset;
    }

    public function getDataset($dataset_id)
    {
        $dataset = $this->bigQueryClient->dataset($dataset_id);
        if ($dataset->exists()) {
            return $dataset;
        }
        return null;
    }

    public function deleteDataset($dataset_id)
    {
        $dataset = $this->getDataset($dataset_id);
        if ($dataset) {
            $result = $dataset->delete();
        }
    }

    public function listDatasets(): array
    {
        $a_datasets = [];
        $datasets = $this->bigQueryClient->datasets();
        foreach($datasets as $dataset) {
            $a_datasets[] = [
                'datasetId' => $dataset->id(),
                'projectId' => $dataset->identity()['projectId'],
                'location' => $dataset->info()['location'],
            ];
        }
        return $a_datasets;
    }

    public function createTable($dataset_id, $table_id, array $a_schema)
    {
        $dataset = $this->getDataset($dataset_id);
        if ($dataset) {
            $table = $dataset->createTable($table_id, ['schema' => $a_schema]);
        }
        return $table;
    }

    public function getTable($dataset_id, $table_id)
    {
        $dataset = $this->getDataset($dataset_id);
        if ($dataset) {
            $table = $dataset->table($table_id);
            if ($table->exists()) {
                return $table;
            }
        }
        return null;
    }

    public function deleteTable($dataset_id, $table_id)
    {
        $dataset = $this->getDataset($dataset_id);
        if ($dataset) {
            $table = $dataset->table($table_id);
            if ($table->exists()) {
                $table->delete();
            }
        }
    }

    public function listTables($dataset_id): array
    {
        $a_tables = [];
        $dataset = $this->getDataset($dataset_id);
        $tables = $dataset->tables();
        foreach($tables as $table) {
            $a_tables[] = [
                'tableId' => $table->id(),
                'datasetId' => $table->identity()['datasetId'],
            ];
        }
        return $a_tables;
    }

}