<?php
namespace Webravo\Persistence\Service;

use Webravo\Infrastructure\Service\ConfigurationServiceInterface;
use Illuminate\Support\Facades\DB;

class ConfigurationService implements ConfigurationServiceInterface
{
    private $settings_db_connection = null;

    private static $configuration_cache = [];

    public function __construct()
    {
        $this->settings_db_connection = env('SETTINGS_DB_CONNECTION', null);
    }

    public function getKey($key, $class = null, $default = null): ?string
    {
        $cached_value = $this->getCachedKey($key, $class);
        if (!is_null($cached_value)) {
            return $cached_value;
        }
        $override = $this->getSettingsOverride($key, $class);
        if (!is_null($override)) {
            $value = $override;
        }
        else {
            /**
             * Laravel specific config and ENV accessors
             */
            if (is_null($class)) {
                $value = env($key, $default);
            } else {
                $value = config($class . '.' . $key, $default);
            }
        }
        $this->setCachedKey($key, $class, $value);
        return $value;
    }

    public function getClass($class = null, $default = null): ?array
    {
        $cached_value = $this->getCachedKey('', $class);
        if (!is_null($cached_value)) {
            return $cached_value;
        }
        $override = $this->getClassOverride($class);
        if (!is_null($override)) {
            $value = $override;
        }
        else {
            /**
             * Laravel specific config and ENV accessors
             */
            $value = config($class, $default);
        }
        $this->setCachedKey('', $class, $value);
        return $value;
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
            // Update cache
            $this->setCachedKey($key, $class, $value);
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
            // Remove from cache
            $this->removeCachedKey($key, $class);
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

    private function getCachedKey($key, $class)
    {
        $cache_key = (!empty($class) ? "$class." : '') . $key;
        if (isset(self::$configuration_cache[$cache_key])) {
            return self::$configuration_cache[$cache_key];
        }
        return null;
    }

    private function setCachedKey($key, $class, $value): void
    {
        $cache_key = (!empty($class) ? "$class." : '') . $key;
        self::$configuration_cache[$cache_key] = $value;
    }

    private function removeCachedKey($key, $class): void
    {
        $cache_key = (!empty($class) ? "$class." : '') . $key;
        if (isset(self::$configuration_cache[$cache_key])) {
            unset(self::$configuration_cache[$cache_key]);
        }
    }
}