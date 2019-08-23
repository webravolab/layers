<?php
use Webravo\Infrastructure\Library\Configuration;

class CdnServiceTest extends TestCase
{
    public function testCdnService()
    {
        $config_service = (new Webravo\Infrastructure\Library\Configuration())::instance();

        $options = [
            'gzip' => false
        ];

        $cdn_service = new \Webravo\Persistence\Service\CdnService($options);

        $image = __DIR__ . '/assets/bsn.png';

        self::assertTrue(file_exists($image), 'Test image does not exists');

        $bucket_name = 'webravo-test-bucket-' . rand(1000,9999);

        $exists = $cdn_service->checkBucketExists($bucket_name);

        if (!$exists) {
            // Create a test bucket
            $success = $cdn_service->createBucket($bucket_name);
            self::assertTrue($success, "Can't create bucket " . $bucket_name);
        }

        $link = $cdn_service->uploadImageToCdn($image, 'test_image.png', $bucket_name);
        self::assertNotNull($link, "Error uploading image to bucket " . $bucket_name);

        $download_file = __DIR__ . '/assets/download.png';

        $exists = $cdn_service->checkImageExists('test_image.png', $bucket_name);
        self::assertTrue($exists, "Failed to check image existence");

        $exists = $cdn_service->checkImageExists('test_image_notfound.png', $bucket_name);
        self::assertFalse($exists, "Failed to check not existent image");

        $cdn_service->downloadImageFromCdn('test_image.png', $download_file, $bucket_name);

        $exists = file_exists($download_file);
        self::assertTrue($exists, "Can't download image from bucket " . $bucket_name);

        unlink($download_file);

        /// $success = $cdn_service->deleteBucket($bucket_name, $force = true);
        // self::assertTrue($success, "Can't delete bucket " . $bucket_name);

        // Test Generic Assets Functions

        $files[] = __DIR__ . '/assets/test.json';
        $files[]  = __DIR__ . '/assets/test.csv';
        $files[]  = __DIR__ . '/assets/test.xml';
        $files[]  = __DIR__ . '/assets/test.txt';


        foreach($files as $file) {
            self::assertTrue(file_exists($file), "Test file $file does not exists");


            $a_parts = pathinfo($file);
            $destination = $a_parts['basename'];

            $link = $cdn_service->uploadAssetToCdn($file, $destination, 3600, true, $bucket_name);
            self::assertNotNull($link, "Error uploading $file to bucket " . $bucket_name);

            $exists = $cdn_service->checkAssetExists($destination, $bucket_name);
            self::assertTrue($exists, "Failed to check $file existence");

            $exists = $cdn_service->checkAssetExists('test.xxx', $bucket_name);
            self::assertFalse($exists, "Failed to check not existent asset");

            $download_file = __DIR__ . '/assets/downloaded.txt';
            $cdn_service->downloadAssetFromCdn($destination, $download_file, $bucket_name);

            $exists = file_exists($download_file);
            self::assertTrue($exists, "Can't download asset $destination from bucket " . $bucket_name);

            $success = $cdn_service->deleteAssetFromCdn($link, $bucket_name);
            self::assertTrue($success, "Can't delete $file from $bucket_name");
        }

        $success = $cdn_service->deleteBucket($bucket_name, $force = true);
        self::assertTrue($success, "Can't delete bucket " . $bucket_name);

    }
}
