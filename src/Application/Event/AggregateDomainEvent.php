<?php
namespace Webravo\Application\Event;

use DateTime;

abstract class AggregateDomainEvent extends GenericEvent
{
    private $_aggregate_type;

    private $_aggregate_id;

    private $_version;

    public function __construct($type, $aggregate_type, $aggregate_id, ?DateTime $occurred_at = null)
    {
        parent::__construct($type, $occurred_at);
        $this->_aggregate_type = $aggregate_type;
        $this->_aggregate_id = $aggregate_id;
    }

    public function withVersion($version)
    {
        $this->_version = $version;
        return $this;
    }

    public function getVersion(): string
    {
        return $this->_version;
    }

    public function getAggregateType()
    {
        return $this->_aggregate_type;
    }

    public function getAggregateId()
    {
        return $this->_aggregate_id;
    }

    public function toArray(): array
    {
        $data = parent::toArray() + [
            'version' => $this->getVersion(),
            'aggregate_type' => $this->getAggregateType(),
            'aggregate_id' => $this->getAggregateId(),
        ];
        return $data;
    }

    public function fromArray(array $data)
    {
        // Get base properties
        parent::fromArray($data);
        if (isset($data['version'])) {
            $this->_version = $data['version'];
        }
        if (isset($data['aggregate_type'])) {
            $this->_aggregate_type = $data['aggregate_type'];
        }
        if (isset($data['aggregate_id'])) {
            $this->_aggregate_id = $data['aggregate_id'];
        }
    }
}