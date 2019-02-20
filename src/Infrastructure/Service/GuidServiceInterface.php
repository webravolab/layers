<?php

namespace Webravo\Infrastructure\Service;

use Webravo\Common\ValueObject\GuidObject;

interface GuidServiceInterface {
    public function generate():GuidObject;
}
