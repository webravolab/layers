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
    
}

