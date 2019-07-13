<?php
namespace Webravo\Infrastructure\Service;

interface DataStoreServiceInterface
{
    /**
     * Inject the datastore connection from external
     * @param $connection
     * @return mixed
     */
    public function setConnection($connection);

    /**
     * Get the current datastore connection
     * @return mixed
     */
    public function getConnection();
}

