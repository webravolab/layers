<?php

namespace Webravo\Persistence\Service;

use Webravo\Infrastructure\Service\FilesystemServiceInterface;
use \Exception;

class FilesystemService implements FilesystemServiceInterface {

    private $path = null;

    public function setPath($path)
    {
        $this->path = app_path($path . '/');
        if (!file_exists($this->path)) {
            mkdir($this->path);
        }
    }

    public function generateNewFilename($mime_type, $name = null)
    {
        switch ($mime_type) {
            case 'png';
                break;
            default:
                throw new Exception('Invalid mime type ' . $mime_type);
                break;
        }
        $file_name = $this->path . time();
        if (!empty($name)) {
            $file_name .= '_' . $name;
        }
        $file_name .= '.' . $mime_type;
        return $file_name;
    }

    public function createNewFile($file_name)
    {
        // TODO: Implement createNewFile() method.
    }

    public function saveFile($file_name, $content)
    {
        // TODO: Implement saveFile() method.
    }

    public function getFile($file_name)
    {
        // TODO: Implement getFile() method.
        return '<image>';
    }
}