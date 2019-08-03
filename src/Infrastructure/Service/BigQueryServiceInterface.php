<?php
namespace Webravo\Infrastructure\Service;

interface BigQueryServiceInterface
{
    /**
     * Inject the connection from external
     * @param $connection
     * @return mixed
     */
    public function setConnection($connection);

    /**
     * Get the current connection
     * @return mixed
     */
    public function getConnection();

    public function createDataset($dataset_id);

    public function getDataset($dataset_id);

    public function deleteDataset($dataset_id);

    public function listDatasets(): array;

    public function createTable($dataset_id, $table_id, array $a_schema);

    public function getTable($dataset_id, $table_id);

    public function deleteTable($dataset_id, $table_id);

    public function listTables($dataset_id): array;

    public function insertRow($dataset_id, $table_id, $a_row, $transaction_id = null);

    public function insertRows($dataset_id, $table_id, $a_rows, $transaction_id = null);

    public function getByKey($dataset_id, $table_id, $key, $value): array;

    public function PaginateRows($dataset_id, $table_id, $pageSize, $cursor = ''): array;

    public function paginateByKey($dataset_id, $table_id, $key, $comparison, $value, $order, $pageSize, $pageCursor = ''): array;

}

