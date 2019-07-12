<?php

namespace tests\Events;

use Webravo\Common\Contracts\DomainEventInterface;
use Webravo\Application\Event\EventInterface;
use Webravo\Application\Event\GenericEvent;
use DateTime;

class TestEvent extends GenericEvent implements DomainEventInterface {

    /**
     * The event name used at Domain level
     * @var string
     */
    private $type = 'TestEvent';

    // Event explicit properties
    private $strValue;
    private $intValue;
    private $floatValue;

    // Event payload to be serialized (if any)
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

    public function setStrValue(?string $value)
    {
        $this->strValue = $value;
    }

    public function getStrValue(): ?string
    {
        return $this->strValue;
    }

    public function setIntValue(?int $value)
    {
        $this->intValue = $value;
    }

    public function getIntValue(): ?int
    {
        return $this->intValue;
    }

    public function setFloatValue(?float $value)
    {
        $this->floatValue = $value;
    }

    public function getFloatValue(): ?float
    {
        return $this->floatValue;
    }


    /*
    public function getSerializedPayload(): string
    {
        return json_encode($this->getPayload());
    }
    */

    public function toArray(): array
    {
        $data = parent::toArray() + [
            'payload' => $this->getPayload(),
            'str_value' => $this->getStrValue(),
            'float_value' => $this->getFloatValue(),
            'int_value' => $this->getIntValue(),
        ];
        return $data;
    }

    public function fromArray(array $data)
    {
        // Get base properties
        parent::fromArray($data);

        // Get custom properties
        if (isset($data['payload'])) {
           $this->setPayload($data['payload']);
        }
        if (isset($data['str_value'])) {
            $this->setStrValue($data['str_value']);
        }
        if (isset($data['int_value'])) {
            $this->setIntValue($data['int_value']);
        }
        if (isset($data['float_value'])) {
            $this->setFloatValue($data['float_value']);
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