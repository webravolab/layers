<?php

namespace Webravo\Persistence\Service;

use Webravo\Infrastructure\Library\Configuration;
use Webravo\Infrastructure\Service\BigQueryServiceInterface;
use Google\Cloud\BigQuery\BigQueryClient;
use Exception;

class BigQueryService implements BigQueryServiceInterface {

    protected $bigQueryClient = null;
    protected $location;


    public function __construct()
    {
        $googleProjectId = Configuration::get('GOOGLE_PROJECT_ID');
        $googleConfigFile = Configuration::get('GOOGLE_APPLICATION_CREDENTIALS');
        $this->location = Configuration::get('GOOGLE_BIGQUERY_LOCATION', null,'europe-west2');

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

    public function createDataset($dataset_id, $custom_options = [])
    {
        $options = $custom_options + [
            'location' => $this->location
        ];

        $dataset = $this->bigQueryClient->createDataset($dataset_id, $options);
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

    public function insertRow($o_table, $a_row, $transaction_id = null)
    {
        $options = [];
        if ($transaction_id) {
            $options['insertId'] = $transaction_id;
        }
        $response = $o_table->insertRow($a_row, $options);
        if (!$response->isSuccessful()) {
            $table_id = $o_table->id();
            $row = $response->failedRows()[0];
            $error = $row['errors'][0]['message'];
            throw (new Exception("[BigQueryService][insertRow][$table_id]: $error"));
        }
    }

    public function insertRows($o_table, $a_rows, $transaction_id = null)
    {
        $options = [];
        if ($transaction_id) {
            $options['insertId'] = $transaction_id;
        }
        $response = $o_table->insertRows($a_rows, $options);
        if (!$response->isSuccessful()) {
            $table_id = $o_table->id();
            $rows = $response->failedRows();
            $error = '';
            foreach($rows as $row) {
                $error .= $row['errors'][0] . PHP_EOL;
            }
            throw (new Exception("[BigQueryService][insertRows][$table_id]: $error"));
        }
    }

    public function getByKey($dataset_id, $table_id, $key, $value): array
    {
        $queryConfig = $this->bigQueryClient->query(
            "SELECT * FROM `{$dataset_id}.{$table_id}` WHERE `$key` = @value"
        )->parameters([
            'value' => $value
        ]);
        $options = [
            'resultLimit' => 0,
        ];
        $result = $this->bigQueryClient->runQuery($queryConfig, $options);
        $a_rows = [];
        $iterator = $result->getIterator();
        foreach($iterator as $row) {
            $a_row = [];
            foreach ($row as $column => $value) {
                $a_row[$column] = $value;
            }
            $a_rows[] = $a_row;
        }
        return $a_rows;
    }

    public function PaginateRows($o_table, $pageSize, $pageCursor = ''): array
    {
        $pageCursor =  empty($pageCursor) ? 0 : $pageCursor;
        $options = [
            'maxResults' => $pageSize,
            'resultLimit' => 0,
            'startIndex' => $pageCursor
        ];
        $rows = $o_table->rows($options);
        $a_rows = [];
        $numRows = 0;
        foreach($rows as $row) {
            $a_row = [];
            foreach ($row as $column => $value) {
                $a_row[$column] = $value;
            }
            $a_rows[] = $a_row;
            if (++$numRows >= $pageSize) {
                break;
            }
        }
        $results = [
            'entities' => $a_rows,
            'page_cursor' => $numRows > 0 ? $pageCursor + $numRows : ''
        ];
        return $results;
    }


    public function paginateByKey($dataset_id, $table_id, $key, $comparison, $value, $order, $pageSize, $pageCursor = ''): array
    {
        $pageCursor =  empty($pageCursor) ? 0 : $pageCursor;
        $order = $order == 'desc' ? 'DESC' : 'ASC';

        $queryConfig = $this->bigQueryClient->query(
            "SELECT * FROM `{$dataset_id}.{$table_id}` WHERE `$key` $comparison @value ORDER BY `$key` $order"
        )->parameters([
            'value' => $value
        ]);
        $options = [
            'maxResults' => $pageSize,
            'resultLimit' => 0,
            'startIndex' => $pageCursor
        ];
        $result = $this->bigQueryClient->runQuery($queryConfig, $options);
        $a_rows = [];
        $numRows = 0;
        $iterator = $result->getIterator();
        foreach($iterator as $row) {
            $a_row = [];
            foreach ($row as $column => $value) {
                $a_row[$column] = $value;
            }
            $a_rows[] = $a_row;
            if (++$numRows >= $pageSize) {
                break;
            }
        }
        $results = [
            'entities' => $a_rows,
            'page_cursor' => $numRows > 0 ? $pageCursor + $numRows : ''
        ];
        return $results;
    }

}