<?php

use Webravo\Infrastructure\Library\Configuration;
use Webravo\Persistence\Datastore\Store\DataStoreCommandStore;
use Webravo\Persistence\Eloquent\Store\EloquentCommandStore;

class CommandStoreTest extends TestCase
{
    public function testEloquentCommandStore()
    {
        $commandStore = new EloquentCommandStore();

        $strParam1 = 'This is a command test';
        $intParam2 = (int)775;
        $floatParam3 = (float)12.58;
        $clsParam4 = [
            'value1' => 'this is value1',
            'value2' => 222,
        ];
        $arrParam5 = [
            'aValue1' => 'array value 1',
            'aValue2' => 2222,
        ];

        $command = new \tests\Commands\TestCommand($strParam1, $intParam2, $floatParam3, $clsParam4, $arrParam5);

        $guid = $command->getGuid();

        $commandStore->Append($command);

        $retrieved_command = $commandStore->getByGuid($guid);

        $this->assertEquals($command->getParam1(), $retrieved_command->getParam1());
        $this->assertEquals($command->getParam2(), $retrieved_command->getParam2());
        $this->assertEquals($command->getParam3(), $retrieved_command->getParam3());
        $this->assertEquals($command->getParam4(), $retrieved_command->getParam4());
        $this->assertEquals($command->getParam5(), $retrieved_command->getParam5());
    }

    public function testDataStoreCommandStore()
    {
        $googleConfigFile = Configuration::get('GOOGLE_APPLICATION_CREDENTIALS');
        self::assertTrue(file_exists($googleConfigFile), "Google Credential file $googleConfigFile does not exists");

        $commandStore = new DataStoreCommandStore();
        $strParam1 = 'This is a command test';
        $intParam2 = (int)775;
        $floatParam3 = (float)12.58;
        $clsParam4 = [
            'timestamp' => (new DateTime())->format(DateTime::RFC3339_EXTENDED),
            'value2' => 222,
        ];
        $arrParam5 = [
            'aValue1' => 'array value 1',
            'aValue2' => 2222,
        ];

        $command = new \tests\Commands\TestCommand($strParam1, $intParam2, $floatParam3, $clsParam4, $arrParam5);

        $commandStore->Append($command);

        $guid = $command->getGuid();

        $retrieved_command = $commandStore->getByGuid($guid);

        $this->assertEquals($command->getParam1(), $retrieved_command->getParam1());
        $this->assertEquals($command->getParam2(), $retrieved_command->getParam2());
        $this->assertEquals($command->getParam3(), $retrieved_command->getParam3());
        $this->assertEquals($command->getParam4(), $retrieved_command->getParam4());
        $this->assertEquals($command->getParam5(), $retrieved_command->getParam5());
    }
}
