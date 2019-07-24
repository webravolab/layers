<?php

use tests\TestProject\Domain\Commands\TestCommand;
use Webravo\Application\Command\GenericCommand;

class GenericCommandTest extends TestCase
{

    public function testGenericCommand()
    {
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

        $command = new TestCommand($strParam1, $intParam2, $floatParam3, $clsParam4, $arrParam5);
        $command->setBindingKey('this is thye binding key');
        $command->setQueueName('test queue');

        $guid = $command->getGuid();

        $a_command_data = $command->toArray();

        $rebuilt_command = GenericCommand::buildFromArray($a_command_data);

        $this->assertEquals($command->getCommandName(), $rebuilt_command->getCommandName());
        $this->assertEquals($command->getBindingKey(), $rebuilt_command->getBindingKey());
        $this->assertEquals($command->getQueueName(), $rebuilt_command->getQueueName());
        $this->assertEquals($command->getCreatedAt()->format(DATE_RFC3339_EXTENDED), $rebuilt_command->getCreatedAt()->format(DATE_RFC3339_EXTENDED));

        $this->assertEquals($command->getParam1(), $rebuilt_command->getParam1());
        $this->assertEquals($command->getParam2(), $rebuilt_command->getParam2());
        $this->assertEquals($command->getParam3(), $rebuilt_command->getParam3());
        $this->assertEquals($command->getParam4(), $rebuilt_command->getParam4());
        $this->assertEquals($command->getParam5(), $rebuilt_command->getParam5());
        $this->assertEquals($command->getSerializedCommand(), $rebuilt_command->getSerializedCommand());
    }
}
