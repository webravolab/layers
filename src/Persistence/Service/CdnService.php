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

    public function __construct($options = [])
    {
        $this->google_config = Configuration::getClass('google');
        if (!is_array($this->google_config) || count($this->google_config) == 0) {
            if ($options == []) {
                // Google Service not configured and no custom options passed
                $this->google_client = null;
                return;
            }
        }
        // Merge custom options to config
        $this->google_config = $this->array_merge_recursive_distinct($this->google_config, $options);

        // Initialize Google Rest Client
        $this->google_client = new Google_Client($this->google_config);
        $this->google_client->useApplicationDefaultCredentials();
        $this->google_client->setScopes([\Google_Service_Storage::DEVSTORAGE_FULL_CONTROL]);
    }

    /**
     * @param string $source
     * @param string $cdn_file
     * @param string|null $bucket_name
     * @return mixed
     * @throws Exception
     */
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

        if (isset($this->google_config['gzip'])) {
            $gzip_enabled = $this->google_config['gzip'];
        }
        else {
            $gzip_enabled = false;
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
        if ($gzip_enabled) {
            // Gzip compress image
            $data = gzcompress($data, 9, ZLIB_ENCODING_GZIP);
        }
        // Create new storage object
        $object = new \Google_Service_Storage_StorageObject();
        // Set destination name
        $object->setName($cdn_file);
        $object->setCacheControl('public, max-age=' . $cache_ttl);
        if ($gzip_enabled) {
            // Add "Content-Encoding:gzip" to image metadata
            $object->setContentEncoding('gzip');
        }
        $result = $storage->objects->insert($bucket_name, $object, array(
            'uploadType' => 'media',
            'mimeType' => $destination_mime_type,
            'data' => $data
        ));
        return $result->getMediaLink();
    }

    /**
     * @param string $url
     * @param string|null $bucket_name
     * @return bool
     * @throws Exception
     */
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
        // Check for standard Google Storage path .../b/<bucket>/o/<object>
        $bucket_pos = strpos($path, '/b/'); // search bucket position
        $object_pos = strpos($path, '/o/'); // search object position
        if ($bucket_pos !== false && $object_pos !== false) {
            $original_bucket_name = substr($path, $bucket_pos +3, $object_pos - $bucket_pos -3);
            $object_name = substr($path, $object_pos +3);
            if ($original_bucket_name != $bucket_name) {
                // The image bucket does not match the given/default bucket
                throw(new \Exception('[CdnService][deleteImageFromCdn] Bucket name of image does not match with url - given: ' . $bucket_name . ' - url: ' . $original_bucket_name));
            }
        }
        else {
            // No standard path: use default bucket and get path as object name
            $object_name = $path;
        }
        try {
            // Access storage service
            $storage = new Google_Service_Storage($this->google_client);
            $result = $storage->objects->delete($bucket_name, $object_name);
            if ($result->getStatusCode() == 204) {
                // No content - it's ok
                return true;
            }
        }
        catch (\Exception $e) {
            if ($e->getCode() == 404) {
                // Image not found ... delete is ok
                return true;
            }
            throw(new \Exception('[CdnService][deleteImageFromCdn] Error deleting image ' . $object_name . ' from bucket ' . $bucket_name . ' - ' . $e->getMessage()));
        }
        return true;
    }

    /**
     * @param string $filename
     * @param string $image_name
     * @return string
     * @throws Exception
     */
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

    /**
     * @param string $url
     * @return bool
     * @throws Exception
     */
    public function deleteScreenshotImage(string $url): bool
    {
        return $this->deleteImageFromCdn($url);
    }

    /**
     * Download an object from Cloud Storage and save it as a local file.
     *
     * @param string $url the name of your Google Cloud object.
     * @param string $destination the local destination to save the encrypted object.
     * @param string $bucketName the name of your Google Cloud bucket (empty for default bucket).
     *
     * @return bool
     */
    public function downloadImageFromCdn(string $url, string $destination, string $bucket_name = null): bool
    {
        if (!$this->google_client) {
            throw(new \Exception('[CdnService][downloadImageFromCdn] Google Cloud Storage configuration missing'));
        }

        if (empty($bucket_name)) {
            if (isset($this->google_config['bucket'])) {
                $bucket_name = $this->google_config['bucket'];
            }
        }

        try {
            // Access storage service
            $storage = new Google_Service_Storage($this->google_client);
            $object = $storage->objects->get( $bucket_name, $url);
            $uri = $object->getMediaLink();
            $http = $this->google_client->authorize();
            $response = $http->get($uri);
            if ($response->getStatusCode() != 200) {
                throw(new \Exception('[CdnService][downloadImageFromCdn] Download failed ' . $response->getStatusCode()));
            }
            file_put_contents($destination, $response->getBody());
            return true;
        } catch (\Exception $e) {
            if ($e->getCode() == 404) {
                // Image not found ...
                return false;
            }
            throw(new \Exception('[CdnService][downloadImageFromCdn] Error downloading image ' . $url . ' from bucket ' . $bucket_name . ' - ' . $e->getMessage()));
        }
    }

    /**
     * Check whether an object exists in the bucket
     * @param string $url
     * @param string|null $bucket_name
     * @return bool
     */
    public function checkImageExists(string $url, string $bucket_name = null): bool
    {
        if (!$this->google_client) {
            throw(new \Exception('[CdnService][checkObjectExists] Google Cloud Storage configuration missing'));
        }

        if (empty($bucket_name)) {
            if (isset($this->google_config['bucket'])) {
                $bucket_name = $this->google_config['bucket'];
            }
        }

        try {
            // Access storage service
            $storage = new Google_Service_Storage($this->google_client);
            $object = $storage->objects->get( $bucket_name, $url);
            return true;
        } catch (\Exception $e) {
            if ($e->getCode() == 404) {
                // Image not found ...
                return false;
            }
            throw(new \Exception('[CdnService][checkObjectExists] Error checking image ' . $url . ' from bucket ' . $bucket_name . ' - ' . $e->getMessage()));
        }

    }


    /**
     * Check whether a bucket exists in current project
     * @param string $bucket_name
     * @return bool
     */
    public function checkBucketExists(string $bucket_name): bool
    {
        if (!$this->google_client) {
            throw(new \Exception('[CdnService][checkBucketExists] Google Cloud Storage configuration missing'));
        }

        try {
            // Access storage service
            $storage = new Google_Service_Storage($this->google_client);
            $bucket = $storage->buckets->get($bucket_name);
            return true;
        } catch (\Exception $e) {
            if ($e->getCode() == 404 || $e->getCode() == 403) {
                // Bucket not found ...
                return false;
            }
            throw(new \Exception('[CdnService][checkBucketExists] Error checking bucket ' . $bucket_name . ' - ' . $e->getMessage()));
        }
    }

    /**
     * Create a new bucket in current project
     * @param string $bucket_name
     * @return bool
     */
    public function createBucket(string $bucket_name): bool
    {
        if (!$this->google_client) {
            throw(new \Exception('[CdnService][createBucket] Google Cloud Storage configuration missing'));
        }
        try {
            $project_id = @$this->google_config['project_id'];
            // Access storage service
            $storage = new Google_Service_Storage($this->google_client);
            // Create new bucket object
            $bucket = new \Google_Service_Storage_Bucket();
            $bucket->setName($bucket_name);
            // $bucket->setLocation('EUROPE-WEST2');
            $storage->buckets->insert($project_id, $bucket);
            return true;
        } catch (\Exception $e) {
            throw(new \Exception('[CdnService][createBucket] Error creating bucket ' . $bucket_name . ' - ' . $e->getMessage()));
        }
    }

    /**
     * Delete a bucket from current project
     * @param string $bucket_name
     * @param bool $force               true to delete all content from bucket before deleting
     * @return bool
     */
    public function deleteBucket(string $bucket_name, bool $force = false): bool
    {
        if (!$this->google_client) {
            throw(new \Exception('[CdnService][deleteBucket] Google Cloud Storage configuration missing'));
        }
        try {
            // Access storage service
            $storage = new Google_Service_Storage($this->google_client);
            // Get bucket object
            $bucket = $storage->buckets->get($bucket_name);

            $objects = $storage->objects->listObjects($bucket_name);

            foreach ($objects["items"] as $object)
            {
                if ($force == true) {
                    $result = $storage->objects->delete($bucket_name, $object->getName());
                    if ($result->getStatusCode() != 204) {
                        // Can't delete
                        return false;
                    }
                }
                else {
                    return false;
                }
            }
            $storage->buckets->delete($bucket_name);
            return true;
        } catch (\Exception $e) {
            throw(new \Exception('[CdnService][deleteBucket] Error deleting bucket ' . $bucket_name . ' - ' . $e->getMessage()));
        }
    }

    /**
     * @param $file_name
     * @return string|null
     */
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


    /**
     * Merge options arrays recursively
     * @param array $array1
     * @param array $array2
     * @return array
     */
    protected function array_merge_recursive_distinct(array &$array1, array &$array2)
    {
        $merged = $array1;
        foreach ($array2 as $key => &$value) {
            if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
                $merged[$key] = $this->array_merge_recursive_distinct($merged[$key], $value);
            } else {
                $merged[$key] = $value;
            }
        }
        return $merged;
    }

}