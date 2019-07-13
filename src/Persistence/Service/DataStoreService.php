<?php

namespace Webravo\Persistence\Service;

use Webravo\Infrastructure\Library\Configuration;
use Webravo\Infrastructure\Service\DataStoreServiceInterface;
use \Google\Cloud\Datastore\DatastoreClient;

class DataStoreService implements DataStoreServiceInterface {

    protected $dataStoreClient = null;

    public function __construct()
    {
        $googleProjectId = Configuration::get('GOOGLE_PROJECT_ID');
        $googleConfigFile = Configuration::get('GOOGLE_APPLICATION_CREDENTIALS');

        $this->setConnection(new DatastoreClient([
            'projectId' => $googleProjectId,
            'keyFilePath' => $googleConfigFile,
            ])
        );
    }

    /**
     * Inject the datastore connection from external
     * @param $connection
     * @return mixed
     */
    public function setConnection($connection)
    {
        $this->dataStoreClient = $connection;
    }

    /**
     * Get the current datastore connection
     * @return mixed
     */
    public function getConnection()
    {
        return $this->dataStoreClient;
    }

}