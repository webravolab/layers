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
            // Not found
        }
        // Search in all declared classes
        $a = get_declared_classes();
        $len = strlen($abstract);
        $r = array_filter($a, function ($value) use ($abstract, $len) {
            return substr($value, -($len)) === $abstract;
        });
        if (count($r)> 0) {
            $abstract = array_shift($r);
            try {
                return app()->make($abstract);
            }
            catch (\Exception $e) {
                // Not found
            }
        }
        return null;
    }
}
