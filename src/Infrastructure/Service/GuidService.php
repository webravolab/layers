<?php
namespace Webravo\Infrastructure\Service;

use Webravo\Common\ValueObject\GuidObject;

use Webpatser\Uuid\Uuid;

class GuidService implements GuidServiceInterface {

    public function generate():GuidObject
    {
        $guid = (string) Uuid::generate();
        return new GuidObject($guid);
    }
}
