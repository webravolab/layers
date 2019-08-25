<?php


namespace Webravo\Application\Event;
use Iterator;

class EventStream implements Iterator
{
    private $_aggregate_type;
    private $_aggregate_id;
    private $_events = [];
    private $_position;

    public function __construct($aggregate_type, $aggregate_id)
    {
        $this->_position = 0;
        $this->_aggregate_type = $aggregate_type;
        $this->_aggregate_id = $aggregate_id;
    }

    public function getAggregateType()
    {
        return $this->_aggregate_type;
    }

    public function getAggregateId()
    {
        return $this->_aggregate_id;
    }

    public function addEventWithVersion(AggregateDomainEvent $event, $version)
    {
        $this->_events[] = $event->withVersion($version);
    }

    public function rewind()
    {
        $this->_position = 0;
    }

    public function next()
    {
        ++$this->_position;
    }

    public function current()
    {
        return $this->_events[$this->_position];
    }

    public function valid()
    {
        return isset($this->_events[$this->_position]);
    }

    public function key()
    {
        return $this->_position;
    }
}