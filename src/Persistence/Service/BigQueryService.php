<?php

namespace Webravo\Persistence\Service;

use Google\Cloud\Core\Exception\BadRequestException;
use Webravo\Infrastructure\Library\Configuration;
use Webravo\Infrastructure\Service\BigQueryServiceInterface;
use Google\Cloud\BigQuery\BigQueryClient;
use Exception;

class BigQueryService implements BigQueryServiceInterface {

    protected $bigQueryClient = null;
    protected $location;

    // Cache current dataset + table
    private $_current_dataset = null;
    private $_current_table = null;

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
        $this->_current_dataset = $dataset;
        return $dataset;
    }

    public function getDataset($dataset_id)
    {
        if ($this->_current_dataset && $this->_current_dataset->id() == $dataset_id) {
            return $this->_current_dataset;
        }
        $dataset = $this->bigQueryClient->dataset($dataset_id);
        if ($dataset->exists()) {
            $this->_current_dataset = $dataset;
            return $dataset;
        }
        $this->_current_dataset = null;
        return null;
    }

    public function deleteDataset($dataset_id)
    {
        $dataset = $this->getDataset($dataset_id);
        if ($dataset) {
            $dataset->delete();
            $this->_current_dataset = null;
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
            $this->_current_table = $table;
            return $table;
        }
        return null;
    }

    public function getTable($dataset_id, $table_id)
    {
        if ($this->_current_dataset && $this->_current_table && $this->_current_dataset->id() == $dataset_id && $this->_current_table->id() == $table_id) {
            return $this->_current_table;
        }
        $dataset = $this->getDataset($dataset_id);
        if ($dataset) {
            $table = $dataset->table($table_id);
            if ($table->exists()) {
                $this->_current_table = $table;
                return $table;
            }
        }
        $this->_current_table = null;
        return null;
    }

    public function deleteTable($dataset_id, $table_id)
    {
        $table = $this->getTable($dataset_id, $table_id);
        if ($table && $table->exists()) {
            $table->delete();
            $this->_current_table = null;
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

    public function insertRow($dataset_id, $table_id, $a_row, $transaction_id = null)
    {
        $table = $this->getTable($dataset_id, $table_id);
        if (!$table || !$table->exists()) {
            throw (new Exception("[BigQueryService][insertRow] dataset $dataset_id : table $table_id does not exists"));
        }
        $options = [];
        if ($transaction_id) {
            $options['insertId'] = $transaction_id;
        }
        $response = $table->insertRow($a_row, $options);
        if (!$response->isSuccessful()) {
            $table_id = $table->id();
            $row = $response->failedRows()[0];
            $a_error = $row['errors'][0];
            $error = $a_error['reason'] . ' - ' . $a_error['location'] . ' - ' . $a_error['message'];
            throw (new Exception("[BigQueryService][insertRow][$table_id]: $error"));
        }
    }

    public function insertRows($dataset_id, $table_id, $a_rows, $transaction_id = null)
    {
        $table = $this->getTable($dataset_id, $table_id);
        if (!$table || !$table->exists()) {
            throw (new Exception("[BigQueryService][insertRows] dataset $dataset_id : table $table_id does not exists"));
        }
        $options = [];
        if ($transaction_id) {
            $options['insertId'] = $transaction_id;
        }
        $response = $table->insertRows($a_rows, $options);
        if (!$response->isSuccessful()) {
            $table_id = $table->id();
            $rows = $response->failedRows();
            $error = '';
            foreach($rows as $row) {
                $error .= $row['errors'][0] . PHP_EOL;
            }
            throw (new Exception("[BigQueryService][insertRows][$table_id]: $error"));
        }
    }

    public function updateRow($dataset_id, $table_id, $a_row, $primary_key = 'guid'): bool
    {
        if (!isset($a_row[$primary_key])) {
            throw (new Exception("[BigQueryService][updateRow][$table_id]: cannot find primary key field $primary_key"));
        }
        $pk_value = $a_row[$primary_key];
        $a_original = $this->getByKey($dataset_id, $table_id, $primary_key, $pk_value, true);
        if (is_array($a_original)) {
            $query = "UPDATE `{$dataset_id}.{$table_id}` SET ";
            $separator = '';
            $parameters = [];
            foreach($a_row as $key => $value) {
                if (isset($a_original[$key]) && $a_original[$key] !== $value) {
                    $query .= $separator . " `$key` = @$key";
                    $separator = ', ';
                    $parameters["$key"] = $value;
                }
            }
            $parameters["pk"] = $pk_value;
            $query .= " WHERE `$primary_key` = @pk";

            $queryConfig = $this->bigQueryClient->query($query)
                ->parameters($parameters);

            $options = [
                'resultLimit' => 0,
            ];
            try {
                $result = $this->bigQueryClient->runQuery($queryConfig, $options);
            }
            catch (BadRequestException $e) {
                return false;
            }
            return true;
        }
        return false;
    }

    public function deleteRow($dataset_id, $table_id, $primary_key_value, $primary_key = 'guid'): bool
    {
        $a_original = $this->getByKey($dataset_id, $table_id, $primary_key, $primary_key_value, true);
        if (is_array($a_original)) {
            $query = "DELETE FROM `{$dataset_id}.{$table_id}` ";
            $parameters = [];
            $parameters["pk"] = $primary_key_value;
            $query .= " WHERE `$primary_key` = @pk";

            $queryConfig = $this->bigQueryClient->query($query)
                ->parameters($parameters);

            $options = [
                'resultLimit' => 0,
            ];
            try {
                $result = $this->bigQueryClient->runQuery($queryConfig, $options);
            }
            catch (BadRequestException $e) {
                return false;
            }
            return true;
        }
        return false;
    }

    public function getByKey($dataset_id, $table_id, $key, $value, $first_only = false): array
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
            if ($first_only) {
                return $a_row;
            }
            $a_rows[] = $a_row;
        }
        return $a_rows;
    }

    public function paginateRows($dataset_id, $table_id, $pageSize, $pageCursor = ''): array
    {
        $table = $this->getTable($dataset_id, $table_id);
        if (!$table || !$table->exists()) {
            throw (new Exception("[BigQueryService][paginateRows] dataset $dataset_id : table $table_id does not exists"));
        }
        $pageCursor =  empty($pageCursor) ? 0 : $pageCursor;
        $options = [
            'maxResults' => $pageSize,
            'resultLimit' => 0,
            'startIndex' => $pageCursor
        ];
        $rows = $table->rows($options);
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