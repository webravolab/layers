<?php

use Webravo\Application\Event\GenericEvent;
use tests\TestProject\Domain\Events\TestEvent;

class GenericEventTest extends TestCase
{

    public function testGenericEvent()
    {
        $event = new TestEvent();
        $event->setStrValue('this is a string');
        $event->setIntValue((int) Rand(1,9999));
        $event->setFloatValue((float) Rand());
        $payload = new stdClass();
        $payload->value = 'this is a test value';
        $payload->number = 175;
        $payload->float = 1.75;
        $event->setPayload($payload);
        $guid = $event->getGuid();

        $a_event_data = $event->toArray();

        $rebuilt_event = GenericEvent::buildFromArray($a_event_data);

        $this->assertEquals($event->getStrValue(), $rebuilt_event->getStrValue());
        $this->assertEquals($event->getIntValue(), $rebuilt_event->getIntValue());
        $this->assertEquals($event->getFloatValue(), $rebuilt_event->getFloatValue());

        $this->assertEquals($event->getSerializedPayload(), $rebuilt_event->getSerializedPayload());
        $this->assertEquals($event->getSerializedEvent(), $rebuilt_event->getSerializedEvent());
    }

}
