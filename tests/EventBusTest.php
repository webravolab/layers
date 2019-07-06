<?php
use Webravo\Persistence\Eloquent\Store\EloquentEventStore;
use Webravo\Application\Event\EventBusDispatcher;
use Webravo\Application\Event\EventStoreBusMiddleware;

class EventBusTest extends TestCase
{

    public function testEloquentEventStore()
    {
        $eventStore = new EloquentEventStore();

        $event = new \tests\events\TestEvent();
        $event->setPayload('test value');
        $guid = $event->getGuid();

        $eventStore->Append($event);

        $retrieved_event = $eventStore->getByGuid($guid);

        $this->assertEquals($event->getPayload(), $retrieved_event->getPayload());
    }

    public function testEventBus()
    {
        $eventStore = new EloquentEventStore();
        $eventLocalDispatcher = new EventBusDispatcher();
        $eventBus = new EventStoreBusMiddleware($eventLocalDispatcher, $eventStore);

        $event = new \tests\events\TestEvent();
        $event->setPayload('test value');
        $guid = $event->getGuid();

        $eventBus->dispatch($event);

        $retrieved_event = $eventStore->getByGuid($guid);

        $this->assertEquals($event->getPayload(), $retrieved_event->getPayload());

    }

}
