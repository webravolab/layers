<?php
namespace Webravo\Common\Domain;

use Webravo\Application\Event\AggregateDomainEvent;
use Webravo\Application\Event\EventInterface;
use Webravo\Application\Event\EventStream;
use Webravo\Application\Event\GenericEvent;

trait EventSourcedTrait
{
    protected $event_stream = null;
    protected $changed_stream = null;

    protected $version = 0;

    public function setEventStream(EventStream $stream)
    {
        // Set past event stream to rebuild the aggregate
        $this->event_stream = $stream;
        // Init and empty changed stream to keep track of new events emitted
        $this->initChangedStream(new EventStream($stream->getAggregateType(), $stream->getAggregateId()));
    }

    public function getEventStream()
    {
        return $this->event_stream;
    }

    public function initChangedStream(EventStream $stream)
    {
        $this->changed_stream = $stream;
    }

    public function getChangedStream()
    {
        return $this->changed_stream;
    }

    public function apply(AggregateDomainEvent $event)
    {
        $this->version++;
        $this->changed_stream->addEventWithVersion($event, $this->version);
        $this->mutate($event);
    }


    public static function rebuildFromHistory(EventStream $stream)
    {
        $instance = new static();
        $instance->setEventStream($stream);
        $instance->replay($stream);
        return $instance;
    }

    public function replay(EventStream $events)
    {
        foreach ($events as $event) {
            $this->version = $event->getVersion();
            $this->mutate($event);
        }
    }

    public function mutate(AggregateDomainEvent $event)
    {
        $mutator = $this->eventMap[get_class($event)] ?? null;
        if ($mutator) {
            return $this->$mutator($event);
        }
    }

    public function getVersion()
    {
        return $this->version;
    }
}