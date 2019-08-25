<?php

namespace tests\TestProject\Domain\Events;

use Webravo\Application\Event\AggregateDomainEvent;
use Webravo\Application\Event\GenericEvent;
use DateTime;

class TestTransactionAddedEvent extends AggregateDomainEvent
{
    /**
     * The event name used at Domain level
     * @var string
     */
    private $type = 'TestTransactionAddedEvent';

    private $aggregate_type = 'Transaction';

    // Event explicit properties
    private $transaction_key;
    private $intValue;
    private $floatValue;

    public function __construct($transaction_key, $aggregate_id, ?DateTime $occurred_at = null) {
        parent::__construct($this->type, $this->aggregate_type, $aggregate_id, $occurred_at);
        $this->aggregate_id = $aggregate_id;
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