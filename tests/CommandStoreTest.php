<?php

use Webravo\Infrastructure\Library\Configuration;
use Webravo\Persistence\Datastore\Store\DataStoreEventStore;
use Webravo\Persistence\Eloquent\Store\EloquentCommandStore;

class CommandStoreTest extends TestCase
{

    public function testEloquentCommandStore()
    {
        $commandStore = new EloquentCommandStore();

        $strParam1 = 'This is a command test';
        $intParam2 = (int)775;
        $floatParam3 = (float)12.58;
        $clsParam4 = new stdClass();
        $clsParam4->value1 = 'this is value1';
        $clsParam4->value2 = 222;
        $arrParam5 = [
            'aValue1' => 'array value 1',
            'aValue2' => 2222,
        ];

        $command = new \tests\Commands\TestCommand($strParam1, $intParam2, $floatParam3, $clsParam4, $arrParam5);

        // $guid = $command->getGuid();

        $commandStore->Append($command);

        $retrieved_command = $commandStore->getByGuid($guid);

        $this->assertEquals($command->getPayload(), $retrieved_command->getPayload());
    }

    public function testDataStoreEventStore()
    {
        $googleConfigFile = Configuration::get('GOOGLE_APPLICATION_CREDENTIALS');
        self::assertTrue(file_exists($googleConfigFile), "Google Credential file $googleConfigFile does not exists");

        $commandStore = new DataStoreEventStore();

        $command = new \tests\Events\TestEvent();
        $command->setPayload('test value');
        $guid = $command->getGuid();

        $commandStore->Append($command);

        $retrieved_command = $commandStore->getByGuid($guid);

        $this->assertEquals($command->getPayload(), $retrieved_command->getPayload());
    }


    public function testDBStoreEventBus()
    {
        $commandStore = new EloquentEventStore();
        $commandLocalDispatcher = new EventBusDispatcher();

        // Instantiate an Event Store using DB as underlying storage
        $commandBus = new EventBucketBusMiddleware($commandLocalDispatcher, $commandStore);

        $command = new \tests\Events\TestEvent();
        $command->setPayload('test value');
        $guid = $command->getGuid();

        $commandBus->dispatch($command);

        $retrieved_command = $commandStore->getByGuid($guid);

        $this->assertEquals($command->getPayload(), $retrieved_command->getPayload());

    }

    public function testDataStoreEventBus()
    {
        $commandStore = new DataStoreEventStore();
        $commandLocalDispatcher = new EventBusDispatcher();

        // Instantiate an Event Store using Google Data Store as underlying storage
        $commandBus = new EventBucketBusMiddleware($commandLocalDispatcher, $commandStore);

        $command = new \tests\Events\TestEvent();

        $payload = new stdClass();
        $payload->value = 'this is a test value';
        $payload->number = 175;
        $payload->float = 1.75;

        $command->setPayload( $payload);

        $guid = $command->getGuid();

        $commandBus->dispatch($command);

        $retrieved_command = $commandStore->getByGuid($guid);

        $this->assertEquals($command->getPayload(), $retrieved_command->getPayload());

        $this->assertEquals($command->getOccurredAt(), $retrieved_command->getOccurredAt());

    }

}
