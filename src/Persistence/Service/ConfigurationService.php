<?php
namespace Webravo\Persistence\Service;

use Webravo\Infrastructure\Service\ConfigurationServiceInterface;
use Illuminate\Support\Facades\DB;

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
        $override = $this->getClassOverride($class);
        if (!is_null($override)) {
            return $override;
        }
        /**
         * Laravel specific config and ENV accessors
         */
        return config($class, $default);
    }

    public function getPublicPath($filename = ''): string {
        return public_path($filename);
    }

    /**
     * Create or Replace a key in settings
     * @param $key
     * @param $value
     * @param null $class
     */
    public function setKey($key, $value, $class = null): void
    {
        try {
            if (empty($this->settings_db_connection)) {
                // Settings database connection not defined
                return;
            }
            if (!is_null($class)) {
                $key = $class . '.' . $key;
            }
            $results = DB::connection($this->settings_db_connection)
                ->select("select * from settings where `key` = ?", [$key]);
            if ($results && count($results) == 1) {
                $results = DB::connection($this->settings_db_connection)
                    ->update("update settings set value = ? where `key` = ?", [$value, $key]);
            }
            else {
                $results = DB::connection($this->settings_db_connection)
                    ->insert("insert into settings(`key`, value) values (? , ?)", [$key, $value]);
            }
        }
        catch (\Exception $e) {
            // Ignore any error
            return;
        }
    }

    /**
     * Delete a key from settings
     * @param $key
     * @param null $class
     */
    public function deleteKey($key, $class = null): void
    {
        try {
            if (empty($this->settings_db_connection)) {
                // Settings database connection not defined
                return;
            }
            if (!is_null($class)) {
                $key = $class . '.' . $key;
            }
            $results = DB::connection($this->settings_db_connection)
                ->delete("delete from settings where `key` = ?", [$key]);
        }
        catch (\Exception $e) {
            // Ignore any error
            return;
        }
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
                ->select("select * from settings where `key` = ?", [$key]);
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

    private function getClassOverride($class): ?array
    {
        try {
            if (empty($this->settings_db_connection)) {
                // Settings database connection not defined
                return null;
            }
            if (is_null($class)) {
                return null;
            }
            $class = $class . '%';
            $results = DB::connection($this->settings_db_connection)
                ->select("select * from settings where `key` like ?", [$class]);
            if ($results && count($results) > 0) {
                $a_results = [];
                foreach($results as $obj) {
                    $a_results[$obj->key] = $obj->value;
                }
                return $a_results;
            }
            return null;
        }
        catch (\Exception $e) {
            // Ignore any settings overide error
            return null;
        }
        return null;
    }


}