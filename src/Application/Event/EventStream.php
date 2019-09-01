<?php

namespace Webravo\Application\Event;
use Iterator;

class EventStream implements Iterator
{
    private $aggregate_type;
    private $aggregate_id;
    private $events = [];
    private $position;

   public function __construct($aggregate_type, $aggregate_id)
    {
        $this->position = 0;
        $this->aggregate_type = $aggregate_type;
        $this->aggregate_id = $aggregate_id;
    }

    public static function createByRawEvents(array $a_events): ?EventStream
    {
        if (count($a_events) == 0) {
            return null;
        }
        $stream = null;
        foreach($a_events as $a_event) {
            $event = null;
            if (isset($a_event['payload']) && is_array($a_event['payload'])) {
                // The payload is a plain array
                $event = AggregateDomainEvent::buildFromArray($a_event['payload']);
            }
            elseif (isset($a_event['payload']) && is_string($a_event['payload'])) {
                // The payload is serialized
                $event = AggregateDomainEvent::buildFromSerializedEvent($a_event['payload']);
            }
            elseif (isset($a_event['class_name'])) {
                // The event is not serialized in payload but is plain (using memory EventSource store)
                $event = AggregateDomainEvent::buildFromArray($a_event);
            }
            else {
                // Skip bad event
                continue;
            }
            if ($event) {
                if (!$stream) {
                    $aggregate_type = $event->getAggregateType();
                    $aggregate_id = $event->getAggregateId();
                    $stream = new self($aggregate_type, $aggregate_id);
                }
                $stream->addEventWithVersion($event);
            }
        }
        return $stream;
    }

    public function getAggregateType()
    {
        return $this->aggregate_type;
    }

    public function getAggregateId()
    {
        return $this->aggregate_id;
    }

    public function addEventWithVersion(AggregateDomainEvent $event, $version = null)
    {
        if ($version) {
            $this->events[] = $event->withVersion($version);
        }
        else {
            $this->events[] = $event;
        }
    }

    public function rewind()
    {
        $this->position = 0;
    }

    public function next()
    {
        ++$this->position;
    }

    public function current()
    {
        return $this->events[$this->position];
    }

    public function valid()
    {
        return isset($this->events[$this->position]);
    }

    public function key()
    {
        return $this->position;
    }

    public function allEventsSinceVersion($version): EventStream
    {
        $new_stream = new EventStream($this->aggregate_type, $this->aggregate_id);
        foreach($this->events as $event) {
            if ($event->getVersion() > $version) {
                $new_stream->addEventWithVersion($event);
            }
        }
        return $new_stream;
   }

   public function allEventsSinceLastSnapshot(): EventStream
   {
       for ($pos = count($this->events)-1; $pos >= 0; $pos--) {
           if ($this->events[$pos] instanceof AggregateDomainSnapshotEvent) {
               break;
           }
       }
       if ($pos > 0) {
           $new_stream = new EventStream($this->aggregate_type, $this->aggregate_id);
           for (;$pos < count($this->events); $pos++) {
               $new_stream->addEventWithVersion($this->events[$pos]);
           }
           return $new_stream;
       }
       return $this;
   }
}