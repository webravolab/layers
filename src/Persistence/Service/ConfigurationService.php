<?php
namespace Webravo\Persistence\Service;

use Webravo\Infrastructure\Service\ConfigurationServiceInterface;
use DB;

class ConfigurationService implements ConfigurationServiceInterface
{
    private $settings_db_connection = null;

    public function __construct()
    {
        $this->settings_db_connection = env('SETTINGS_DB_CONNECTION', null);
    }

    public function getKey($key, $class = null, $default = null): ?string
    {
        $override = $this->getSettingsOverride($key, $class);
        if (!is_null($override)) {
            return $override;
        }
        /**
         * Laravel specific config and ENV accessors
         */
        if (is_null($class)) {
            return env($key, $default);
        }
        return config($class.'.'.$key, $default);
    }

    public function getClass($class = null, $default = null): ?array
    {
        /**
         * Laravel specific config and ENV accessors
         */
        return config($class, $default);
    }

    public function getPublicPath($filename = ''): string {
        return public_path($filename);
    }

    private function getSettingsOverride($key, $class = null, $default = null): ?string
    {
        try {
            if (empty($this->settings_db_connection)) {
                // Settings database connection not defined
                return null;
            }
            if (!is_null($class)) {
                $key = $class . '.' . $key;
            }
            $results = DB::connection($this->settings_db_connection)
                ->select("select * from settings where `key` = '" . $key . "'");
            if ($results && count($results) == 1) {
                return $results[0]->value;
            }
        }
        catch (\Exception $e) {
            // Ignore any settings overide error
            return null;
        }
        return null;
    }
}