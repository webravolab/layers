<?php

namespace Webravo\Persistence\Service;

use Google\Cloud\Storage\Bucket;
use Webravo\Infrastructure\Service\CdnServiceInterface;
use Webravo\Infrastructure\Library\Configuration;
use \Exception;
use Cdn;
use Google_Client;
use Google\Cloud\Storage\StorageClient;
use Google_Service_Storage;
use Google_Service_Storage_StorageObject;

class CdnService implements CdnServiceInterface {

    private $google_client;
    private $google_config;

    public function __construct()
    {
        $this->google_config = Configuration::getClass('google');
        if (!is_array($this->google_config) || count($this->google_config) == 0) {
            // Google Service not configured
            $this->google_client = null;
            return;
        }
        $this->google_client = new Google_Client($this->google_config);
        $this->google_client->useApplicationDefaultCredentials();
        $this->google_client->setScopes([\Google_Service_Storage::DEVSTORAGE_FULL_CONTROL]);
    }

    public function uploadImageToCdn(string $source, string $cdn_file, string $bucket_name = null)
    {
        if (!$this->google_client) {
            throw(new \Exception('[CdnService][uploadImageToCdn] Google Cloud Storage configuration missing'));
        }

        if (!file_exists($source)) {
            throw(new \Exception('[CdnService][uploadImageToCdn] Invalid source file ' . $source));
        }

        if (filesize($source) > 3000000) {
            throw(new \Exception('[CdnService][uploadImageToCdn] source file is > 3MB: ' . $source));
        }

        if (empty($bucket_name)) {
            if (isset($this->google_config['bucket'])) {
                $bucket_name = $this->google_config['bucket'];
            }
        }

        if (isset($this->google_config['image_cache_ttl'])) {
            $cache_ttl = $this->google_config['image_cache_ttl'];
        }
        else {
            $cache_ttl = 86400; // default cache: 1 day
        }

        // Check source file mime type
        $source_mime_type = $this->getImageMimeType($source);
        if (is_null($source_mime_type)) {
            throw(new \Exception('[CdnService][uploadImageToCdn] source is not a valid image file: ' . $source));
        }

        // Strip leading "/" from destination file
        while(substr($cdn_file,0,1) == '/') {
            $cdn_file = substr($cdn_file,1);
        }

        // Check destination file mime type
        $destination_mime_type = $this->getImageMimeType($cdn_file);
        if (is_null($destination_mime_type)) {
            throw(new \Exception('[CdnService][uploadImageToCdn] destination is not a valid image file: ' . $cdn_file));
        }
        
        // Access storage service
        $storage = new Google_Service_Storage($this->google_client);

        // Access bucket
        try {
            $bucket = $storage->buckets->get($bucket_name);
        }
        catch (\Exception $e) {
            throw(new \Exception('[CdnService][uploadImageToCdn] error or invalid bucket name: ' . $bucket_name));
        }

        // Read file
        $file = fopen($source, 'r');
        $data = fread($file,3000000);

        // Create new storage object
        $object = new \Google_Service_Storage_StorageObject();
        // Set destination name
        $object->setName($cdn_file);
        $object->setCacheControl('public, max-age=' . $cache_ttl);
        $result = $storage->objects->insert($bucket_name, $object, array(
            'uploadType' => 'media',
            'mimeType' => $destination_mime_type,
            'data' => $data
        ));
        return $result->getMediaLink();
    }


    public function deleteImageFromCdn(string $url, string $bucket_name = null): bool
    {
        if (!$this->google_client) {
            throw(new \Exception('[CdnService][deleteImageFromCdn] Google Cloud Storage configuration missing'));
        }

        if (empty($bucket_name)) {
            if (isset($this->google_config['bucket'])) {
                $bucket_name = $this->google_config['bucket'];
            }
        }

        // Url format for images
        // https://www.googleapis.com/download/storage/v1/b/<BUCKET>/o/<OBJECT>?generation=<GENERATION>&alt=media

        // Extract object name from url
        $url = urldecode($url);
        $a_parts = parse_url($url);
        if (!isset($a_parts['path'])) {
            return false;
        }
        $path = $a_parts['path'];
        $bucket_pos = strpos($path, '/b/'); // search bucket position
        $object_pos = strpos($path, '/o/'); // search object position
        if ($bucket_pos === false || $object_pos === false) {
            return false;
        }
        $original_bucket_name = substr($path, $bucket_pos +3, $object_pos - $bucket_pos -3);
        $object_name = substr($path, $object_pos +3);
        if ($original_bucket_name != $bucket_name) {
            // The image bucket does not match the given/default bucket
            throw(new \Exception('[CdnService][deleteImageFromCdn] Bucket name of image does not match with url - given: ' . $bucket_name . ' - url: ' . $original_bucket_name));
        }

        // Access storage service
        $storage = new Google_Service_Storage($this->google_client);

        $result = $storage->objects->delete($bucket_name, $object_name);

        return true;
    }

    public function saveScreenshotImage(string $filename, string $image_name): string
    {
        if (file_exists($filename)) {
            $url = Cdn::image($filename, [
                'name' => $image_name,
                'type' => 'jpg',
                'mode' => 'crop',
                'size' => '1280x1024',
                'position' => '0,0',
                'quality' => 85,
                'background' => 'transparent'
                ]
            );
            if (is_null($url)) {
                throw(new \Exception('[CdnService][saveScreenshotImage] Cannot save image on Cdn:' . $filename . ' - ' . $image_name));
            }
            $a_parts = parse_url($url);
            if ($a_parts !== false && isset($a_parts['path'])) {
                $local_file_name = Configuration::getPublicPath($a_parts['path']);
                if (file_exists($local_file_name)) {
                    // Upload result image to Google Storage CDN
                    $url = $this->uploadImageToCdn($local_file_name, $image_name);
                    if ($url) {
                        unlink($local_file_name);
                    }
                }
            }
            return $url;
        }
        return '';
    }

    public function deleteScreenshotImage(string $url): bool
    {
        return $this->deleteImageFromCdn($url);
    }


    public function getImageMimeType($file_name): ?string
    {
        $image_types = [
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif'
        ];

        try {
            // Extract real image extension from path
            $a_parts = pathinfo($file_name);
            if (isset($a_parts['extension'])) {
                $extension = strtolower($a_parts['extension']);
                if (array_key_exists($extension, $image_types)) {
                    return $image_types[$extension];
                }
            }
            return null;
        }
        catch (\Exception $e) {
            return null;
        }
    }

    
}