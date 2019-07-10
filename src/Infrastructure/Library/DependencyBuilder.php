<?php
namespace Webravo\Infrastructure\Library;

class DependencyBuilder
{
    /**
     * Resolve abstract interface to concrete instance using Laravel DI 
     * @param $abstract
     * @return instance or null
     */
    public static function resolve($abstract) 
    {
        try {
            return app()->make($abstract);
        }
        catch (\Exception $e) {
            return null;
        }
    }
}
