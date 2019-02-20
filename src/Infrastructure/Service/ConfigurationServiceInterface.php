<?php

namespace Webravo\Infrastructure\Service;

interface ConfigurationServiceInterface
{
    public function getKey($key, $class = null, $default = null): ?string;

    public function getClass($class = null, $default = null): ?array;

    public function getPublicPath($filename = ''): string;

}

