<?php

namespace tests\Events;

use Webravo\Common\Contracts\DomainEventInterface;
use Webravo\Application\Event\EventInterface;
use Webravo\Application\Event\GenericEvent;
use DateTime;

class TestEvent extends GenericEvent implements DomainEventInterface {

    private $type = 'tests\Events\TestEvent';

    private $payload;

    public function __construct(?DateTime $occurred_at = null) {
        parent::__construct($this->type, $occurred_at);
    }

    public function setPayload($value)
    {
        $this->payload = $value;
    }

    public function getPayload()
    {
        return $this->payload;
    }

    /*
    public function getSerializedPayload(): string
    {
        return json_encode($this->getPayload());
    }
    */

    public function toArray(): array
    {
        $data = [
            'guid' => $this->getGuid(),
            'type' => $this->getType(),
            'occurred_at' => $this->getOccurredAt(),
            'payload' => $this->getPayload(),
        ];
        return $data;
    }

    public function fromArray(array $data)
    {
        if (isset($data['guid'])) {
            $this->setGuid($data['guid']);
        }
        if (isset($data['type'])) {
            $this->setType($data['type']);
        }
        if (isset($data['occurred_at'])) {
            $this->setOccurredAt($data['occurred_at']);
        }
        if (isset($data['payload'])) {
           $this->setPayload($data['payload']);
        }
    }

    public static function buildFromArray(array $data): EventInterface
    {
        if (isset($data['payload'])) {
            if (isset($data['type']) && isset($data['occurred_at'])) {
                return self::construct($data['occurred_at']);
            }
        }
        throw(new EventException('Bad serialized event: ' . self::getType()));
    }
}