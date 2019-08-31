<?php

use tests\TestProject\Domain\Entity\TestEntity;
use tests\TestProject\Persistence\DataStore\TestDataStoreTable;
use tests\TestProject\Persistence\Hydrator\TestHydrator;
use Webravo\Infrastructure\Library\Configuration;
use Webravo\Common\ValueObject\DateTimeObject;
use Webravo\Persistence\Service\DataStoreService;
use Faker\Factory;
use Webravo\Application\Event\EventStream;
use Webravo\Persistence\Datastore\Store\DataStoreEventStreamStore;

class DataStoreEventStoreTest extends TestCase
{
    public function testDataStoreEventStore()
    {

        $googleConfigFile = Configuration::get('GOOGLE_APPLICATION_CREDENTIALS');
        self::assertTrue(file_exists($googleConfigFile), "Google Credential file $googleConfigFile does not exists");

        $dataStoreClient = new DataStoreService();
        $event_store = new DataStoreEventStreamStore();

        $faker = Factory::create();
        $aggregate_id = $faker->numberBetween(1000,100000);
        $aggregate_type = 'TestTransaction';

        $event_stream = new EventStream($aggregate_type, $aggregate_id);

        $event = new \tests\TestProject\Domain\Events\TestTransactionChangedStatusEvent($aggregate_id, 'STATUS_1');
        $event_stream->addEventWithVersion($event, 1);

        $event = new \tests\TestProject\Domain\Events\TestTransactionChangedStatusEvent($aggregate_id, 'STATUS_2');
        $event_stream->addEventWithVersion($event, 2);

        $event = new \tests\TestProject\Domain\Events\TestTransactionChangedStatusEvent($aggregate_id, 'STATUS_3');
        $event_stream->addEventWithVersion($event, 3);

        $event_store->addStreamToAggregateId( $event_stream, $aggregate_type, $aggregate_id);

        $stream = $event_store->getEventStreamByAggregateId($aggregate_type, $aggregate_id);

        foreach($stream as $idx => $event) {
            $this->assertEquals($idx+1, $event->getVersion(), "Bad version read from stream $idx");
        }
    }

}

