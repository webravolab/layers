<?php
namespace Webravo\Common\Domain;

use Webravo\Application\Event\AggregateDomainEvent;
use Webravo\Application\Event\EventInterface;
use Webravo\Application\Event\EventStream;
use Webravo\Application\Event\GenericEvent;

trait EventSourcedTrait
{
    protected $event_stream = null;

    protected $version = 0;

    public function setEventStream(EventStream $stream)
    {
        $this->event_stream = $stream;
    }

    public function getEventStream()
    {
        return $this->event_stream;
    }

    public function recordAndApplyThat(AggregateDomainEvent $event)
    {
        $this->version++;
        $this->event_stream->addEventWithVersion($event, $this->version);
        $this->apply($event);
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
            $this->apply($event);
        }
    }

    public function apply(EventInterface $event)
    {
        $applier = $this->eventMap[get_class($event)] ?? null;
        if ($applier) {
            return $this->$applier($event);
        }
    }

}