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

    public static function createByRawEvents(array $a_events): ?EventStream
    {
        if (count($a_events) == 0) {
            return null;
        }
        $stream = null;
        foreach($a_events as $a_event) {
            $event = AggregateDomainEvent::buildFromArray($a_event);
            $version = $event->getVersion();
            if (!$stream) {
                $aggregate_type = $event->getAggregateType();
                $aggregate_id = $event->getAggregateId();
                $stream = new self($aggregate_type, $aggregate_id);
            }
            $stream->addEventWithVersion($event, $version);
        }
        return $stream;
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