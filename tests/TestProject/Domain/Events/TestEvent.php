<?php

namespace tests\TestProject\Domain\Events;

use Webravo\Application\Event\EventInterface;
use Webravo\Application\Event\GenericEvent;
use DateTime;

class TestEvent extends GenericEvent
{
    /**
     * The event name used at Domain level
     * @var string
     */
    private $type = 'TestEvent';

    // Event explicit properties
    private $strValue;
    private $intValue;
    private $floatValue;


    public function __construct(?DateTime $occurred_at = null) {
        parent::__construct($this->type, $occurred_at);
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
}