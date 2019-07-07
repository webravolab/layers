<?php
use Webravo\Common\ValueObject\DateTimeObject;

class DateTimeObjectTest extends TestCase
{

    public function testDateTimeObject()
    {
        $d = new DateTime();
        $a = new DateTimeObject($d);
        $v = $a->getValue();
        $this->assertEquals($d->format('H') ,$v->format('H'), "DateTimeObject test 1 failed");

        $isoDate = (string) $a;

        $this->assertEquals($isoDate ,$v->format(DateTime::ISO8601), "DateTimeObject test 2 failed");

        $b = DateTimeObject::fromString($d->format('Y-m-d H:i:s.u'));

        $this->assertTrue($b->isEqual($a));
    }

}

