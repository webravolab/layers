<?php
namespace Webravo\Infrastructure\Library;

class DependencyBuilder
{
    /**
     * Resolve abstract interface to concrete instance using Laravel DI 
     * @param $abstract
     * @return instance
     */
    public static function resolve($abstract) 
    {
        return app()->make($abstract);
    }
}
