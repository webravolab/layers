<?php

namespace tests\TestProject\Domain\Events;

use Webravo\Application\Event\GenericEvent;
use DateTime;

class TestTransactionAddedEvent extends GenericEvent
{
    /**
     * The event name used at Domain level
     * @var string
     */
    private $type = 'TestTransactionAddedEvent';

    // Event explicit properties
    private $transaction_key;
    private $intValue;
    private $floatValue;

    public function __construct($transaction_key, ?DateTime $occurred_at = null) {
        parent::__construct($this->type, $occurred_at);
        $this->transaction_key = $transaction_key;
    }

    public function setTransactionKey($transaction_key)
    {
        $this->transaction_key = $transaction_key;
    }

    public function getTransactionKey()
    {
        return $this->transaction_key;
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
                'transaction_key' => $this->getTransactionKey(),
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
        if (isset($data['transaction_key'])) {
            $this->setTransactionKey($data['transaction_key']);
        }
        if (isset($data['int_value'])) {
            $this->setIntValue($data['int_value']);
        }
        if (isset($data['float_value'])) {
            $this->setFloatValue($data['float_value']);
        }
    }
}