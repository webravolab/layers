<?php

namespace Webravo\Common\Contracts;

use Webravo\Application\Event\EventInterface;

interface DomainEventInterface extends EventInterface {

    public function getGuid();

    public function getOccurredAt();

    /**
     * set event type (needed when event is rebuilded from raw data)
     * @param $type
     * @return mixed
     */
    public function setType($type);

    public function setPayload($value);

    public function getPayload();

    // public function getSerializedPayload(): string;

}
