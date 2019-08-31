<?php

namespace tests\TestProject\Domain\Events;

use Webravo\Application\Event\AggregateDomainEvent;
use DateTime;

class TestTransactionChangedStatusEvent extends AggregateDomainEvent
{
    /**
     * The event name used at Domain level
     * @var string
     */
    private $type = 'TestTransactionChangedStatusEvent';

    private $aggregate_type = 'TestTransaction';

    // Event explicit properties
    private $status;

    public function __construct($aggregate_id, $status, ?DateTime $occurred_at = null) {
        parent::__construct($this->type, $this->aggregate_type, $aggregate_id, $occurred_at);
        $this->status = $status;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function toArray(): array
    {
        $data = parent::toArray() + [
                'status' => $this->getStatus(),
            ];
        return $data;
    }

    public function fromArray(array $data)
    {
        // Get base properties
        parent::fromArray($data);

        // Get custom properties
        if (isset($data['status'])) {
            $this->setStatus($data['status']);
        }
    }
}