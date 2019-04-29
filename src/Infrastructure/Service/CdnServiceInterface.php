<?php

namespace Webravo\Infrastructure\Service;

interface CdnServiceInterface
{
    /**
     * @param string $file_name     The local filename to save on CDN
     * @param string $image_name    The image name to save on CDN
     * @return string               The Cdn public URL where the image has been stored
     */
    public function saveScreenshotImage(string $file_name, string $image_name): string;

    /**
     * @param string $url           The media url
     * @return mixed
     */
    public function deleteScreenshotImage(string $url): bool;

    /**
     * @param string $source        The source image path on local dilesystem
     * @param string $bucket_name   The destination bucket (Google Storage specific), otherwise empty for default CDN bucket
     * @param string $remote_file   The image path name (include folders) of destination
     * @return mixed
     */
    public function uploadImageToCdn(string $source, string $remote_file, string $bucket_name = null);


    /**
     * @param string $url           The image url
     * @param string $bucket_name   The bucket name (Google Storage specific), otherwise empty for default CDN bucket
     * @return bool
     */
    public function deleteImageFromCdn(string $url, string $bucket_name = null): bool;


    /**
     * Download an object from Cloud Storage and save it as a local file.
     *
     * @param string $url the name of your Google Cloud object.
     * @param string $destination the local destination to save the encrypted object.
     * @param string $bucketName the name of your Google Cloud bucket (empty for default bucket).
     *
     * @return bool
     */
    public function downloadImageFromCdn(string $url, string $destination, string $bucket_name = null): bool;

    /**
     * Check whether a bucket exists in current project
     * @param string $bucket_name
     * @return bool
     */
    public function checkBucketExists(string $bucket_name): bool;

    /**
     * Create a new bucket in current project
     * @param string $bucket_name
     * @return bool
     */
    public function createBucket(string $bucket_name): bool;

    /**
     * Delete a bucket from current project
     * @param string $bucket_name
     * @param bool $force               true to delete all content from bucket before deleting
     * @return bool
     */
    public function deleteBucket(string $bucket_name, bool $force = false): bool;

}

