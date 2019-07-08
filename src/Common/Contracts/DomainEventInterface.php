<?php

namespace Webravo\Common\Contracts;

use Webravo\Application\Event\EventInterface;

interface DomainEventInterface extends EventInterface {

    public function getGuid();

    public function getOccurredAt();

    public function setType($type);

    public function setPayload($value);

    public function getPayload();

    public function getSerializedPayload(): string;

}
