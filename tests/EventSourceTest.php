<?php
use Webravo\Infrastructure\Library\Configuration;
// use Faker\Factory;
use tests\TestProject\Domain\AggregateRoot\TestTransaction;
use tests\TestProject\Domain\Events\TestTransactionAddedEvent;

class EventSourceTest extends TestCase
{
    public function testEventSourceOne()
    {
        $googleConfigFile = Configuration::get('GOOGLE_APPLICATION_CREDENTIALS');
        self::assertTrue(file_exists($googleConfigFile), "Google Credential file $googleConfigFile does not exists");

        $event = new TestTransactionAddedEvent("abcd");
        $t = TestTransaction::newTransaction();
        $t->apply($event);


    }
}