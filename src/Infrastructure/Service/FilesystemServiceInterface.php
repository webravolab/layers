<?php

namespace Webravo\Infrastructure\Service;

interface FilesystemServiceInterface
{
    public function setPath($path);

    public function generateNewFilename($mime_type, $name = null);

    public function createNewFile($file_name);

    public function saveFile($file_name, $content);

    public function getFile($file_name);
}

