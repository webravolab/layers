<?php
namespace Webravo\Application\Event;

use DateTime;

class AggregateDomainSnapshotEvent extends AggregateDomainEvent
{
    private $_aggregate_type;

    private $_aggregate_id;

    private $_version;

    public function __construct($type, $aggregate_type, $aggregate_id, array $a_payload, ?DateTime $occurred_at = null)
    {
        parent::__construct($type, $aggregate_type, $aggregate_id);
        $this->setPayload(serialize($a_payload));
    }

    public function getAggregateAttributes()
    {
        return unserialize($this->getPayload());
    }
}