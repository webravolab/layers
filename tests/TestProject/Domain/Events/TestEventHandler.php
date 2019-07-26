<?php
namespace tests\TestProject\Domain\Events;

use Webravo\Application\Event\EventInterface;
use Webravo\Application\Event\EventHandlerInterface;
use TestEvent;

class TestEventHandler implements EventHandlerInterface {

    public function handle(EventInterface $event): void
    {
        if ($event instanceof TestEvent) {
            $payload = $event->getPayload();
        }
    }

    public static function listenTo(): string
    {
        return TestEvent::class;
    }

}