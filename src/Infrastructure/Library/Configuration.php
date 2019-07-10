<?php

namespace Webravo\Infrastructure\Library;

use Webravo\Infrastructure\Service\ConfigurationServiceInterface;
use Webravo\Infrastructure\Library\DependencyBuilder;
use Webravo\Persistence\Service\ConfigurationService;

class Configuration
{
    private static $instance = null;

    public static function instance()
    {
        if (null === static::$instance) {
            static::$instance = DependencyBuilder::resolve('Webravo\Infrastructure\Service\ConfigurationServiceInterface');
            if (null === static::$instance) {
                // Default concrete
                static::$instance = new ConfigurationService();
            }
        }
        return static::$instance;
    }

    public static function get($key, $class = null, $default = null): ?string {
        return static::instance()->getKey($key, $class, $default);
    }

    public static function getClass($class, $default = []): array {
        return static::instance()->getClass($class, $default);
    }

    public static function getPublicPath($filename = ''): string {
        return static::instance()->getPublicPath($filename);
    }

    public static function set($key, $class = null, $value): void {
        static::instance()->setKey($key, $value, $class);
    }

    public static function delete($key, $class = null): void {
        static::instance()->deleteKey($key, $class);
    }
}
