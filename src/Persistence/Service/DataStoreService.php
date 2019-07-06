<?php

namespace Webravo\Persistence\Service;

use Webravo\Infrastructure\Library\Configuration;
use Webravo\Infrastructure\Service\DataStoreServiceInterface;
use \Google\Cloud\Datastore\DatastoreClient;
use \Exception;

class DataStoreService implements DataStoreServiceInterface {

    protected $dataStoreClient = null;

    public function __construct()
    {
        $googleProjectId = Configuration::get('GOOGLE_PROJECT_ID');
        $googleConfigFile = Configuration::get('GOOGLE_APPLICATION_CREDENTIALS');

        $this->dataStoreClient = new DatastoreClient([
            'projectId' => $googleProjectId,
            'keyFilePath' => $googleConfigFile,
        ]);
    }

    /**
     * Get the DataStore instance
     * @return DatastoreClient|null
     */
    public function connection()
    {
        return $this->dataStoreClient;
    }



}