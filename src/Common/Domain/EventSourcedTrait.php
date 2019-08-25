<?php
namespace Webravo\Common\Domain;

use Webravo\Application\Event\EventInterface;
use Webravo\Application\Event\EventStream;
use Webravo\Application\Event\GenericEvent;

trait EventSourcedTrait
{
    protected $event_stream = [];

    protected $version = 0;

    protected function recordAndApplyThat(GenericEvent $event)
    {
        $this->version++;
        $this->event_stream[] = $event;
        $this->apply($event);
    }

    protected static function rebuildFromHistory(EventStream $events)
    {
        $instance = new static();
        $instance->replay($events);
        return $instance;
    }

    protected function replay(EventStream $events)
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