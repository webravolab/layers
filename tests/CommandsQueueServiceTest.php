<?php
use Webravo\Application\Service\CommandsQueueService;

use tests\TestProject\Domain\Commands\TestWithoutHandlerCommand;
use tests\TestProject\Domain\Commands\TestCommand;
use tests\TestProject\Domain\Commands\TestHandler;


class CommandsQueueServiceTest extends TestCase
{

    public function testQueue2DiscardAndStore2DiscardCommandsQueueService()
    {

        // putenv('COMMAND_QUEUE_SERVICE=discard');
        // putenv('COMMAND_STORE_SERVICE=discard');

        // Mock Logger
        $loggerSpy = Mockery::spy('Psr\Log\LoggerInterface');

        app()->instance('Psr\Log\LoggerInterface', $loggerSpy);

        // inline version
        // app()->instance('Psr\Log\LoggerInterface',
        //    Mockery::spy('Psr\Log\LoggerInterface', function ($mock) {
        //        $mock->shouldReceive('debug')
        //            ->withAnyArgs()->andReturnValues([true]);
        //    })
        // );

        $service = new CommandsQueueService([
            'command_queue_service' => 'discard',
            'command_store_service' => 'discard',
        ]);

        /*
        $payload = rand(1,999);

        $command = new TestCommand($payload);
        */

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

        $command = new TestCommand($strParam1, $intParam2, $floatParam3, $clsParam4, $arrParam5);

        $result = $service->dispatchCommand($command);

        $loggerSpy->shouldHaveReceived('debug')->withArgs(['Fire command: ' . $command->getCommandName()]);
    }


    public function testQueue2SyncAndStore2DiscardCommandsQueueService()
    {

        // putenv('COMMAND_QUEUE_SERVICE=sync');
        // putenv('COMMAND_STORE_SERVICE=discard');

        // Mock Logger
        $loggerSpy = Mockery::spy('Psr\Log\LoggerInterface');

        // Mock Command Handler
        $handler = Mockery::spy(TestHandler::class);

        app()->instance('Psr\Log\LoggerInterface', $loggerSpy);
        app()->instance('tests\TestProject\Domain\Commands\TestHandler', $handler);

        $service = new CommandsQueueService([
            'command_queue_service' => 'sync',
            'command_store_service' => 'discard',
        ]);

        /*
        $payload = rand(1,999);

        $command = new TestCommand($payload);
        */

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

        $command = new TestCommand($strParam1, $intParam2, $floatParam3, $clsParam4, $arrParam5);

        $handler->shouldReceive('handle')->withArgs([$command]);

        $result = $service->dispatchCommand($command);

        $loggerSpy->shouldHaveReceived('debug')->withArgs(['Fire command: ' . $command->getCommandName()]);

        $handler->shouldHaveReceived('handle');
    }

    public function testQueue2DiscardAndStore2DbCommandsQueueService()
    {
        // putenv('COMMAND_QUEUE_SERVICE=discard');
        // putenv('COMMAND_STORE_SERVICE=db');

        // Mock Logger
        $loggerSpy = Mockery::spy('Psr\Log\LoggerInterface');

        $store = Mockery::spy('Webravo\Persistence\Eloquent\Store\EloquentCommandStore');

        app()->instance('Psr\Log\LoggerInterface', $loggerSpy);
        app()->instance('Webravo\Persistence\Eloquent\Store\EloquentCommandStore', $store);

        $service = new CommandsQueueService([
            'command_queue_service' => 'discard',
            'command_store_service' => 'db',
        ]);

        /*
        $payload = rand(1,999);

        $command = new TestCommand($payload);
        */

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

        $command = new TestCommand($strParam1, $intParam2, $floatParam3, $clsParam4, $arrParam5);

        $result = $service->dispatchCommand($command);

        $loggerSpy->shouldHaveReceived('debug')->withArgs(['Fire command: ' . $command->getCommandName()]);

        $store->shouldHaveReceived('append');

    }

    public function testQueue2DiscardAndStore2DatastoreCommandsQueueService()
    {
        // putenv('COMMAND_QUEUE_SERVICE=discard');
        // putenv('COMMAND_STORE_SERVICE=datastore');

        // Mock Logger
        $loggerSpy = Mockery::spy('Psr\Log\LoggerInterface');

        $store = Mockery::spy('Webravo\Persistence\Datastore\Store\DataStoreCommandStore');

        app()->instance('Psr\Log\LoggerInterface', $loggerSpy);
        app()->instance('Webravo\Persistence\Datastore\Store\DataStoreCommandStore', $store);

        $service = new CommandsQueueService([
            'command_queue_service' => 'discard',
            'command_store_service' => 'datastore',
        ]);

        /*
        $payload = rand(1,999);

        $command = new TestCommand($payload);
        */

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

        $command = new TestCommand($strParam1, $intParam2, $floatParam3, $clsParam4, $arrParam5);

        $result = $service->dispatchCommand($command);

        $loggerSpy->shouldHaveReceived('debug')->withArgs(['Fire command: ' . $command->getCommandName()]);

        $store->shouldHaveReceived('append');

    }

    public function testQueue2DbAndStore2DiscardCommandsQueueService()
    {

        // putenv('COMMAND_QUEUE_SERVICE=db');
        // putenv('COMMAND_STORE_SERVICE=discard');

        // Mock Logger
        $loggerSpy = Mockery::spy('Psr\Log\LoggerInterface');

        // Mock Queue
        // (EloquentJobStore cannot be spied !!)
        $queue = Mockery::mock('Webravo\Persistence\Eloquent\Store\EloquentJobStore');

        app()->instance('Psr\Log\LoggerInterface', $loggerSpy);
        app()->instance('Webravo\Persistence\Eloquent\Store\EloquentJobStore', $queue);

        $queue->shouldReceive('createQueue')->withAnyArgs()->andReturnValues([1]);
        $queue->shouldReceive('bindQueue')->withAnyArgs();

        $service = new CommandsQueueService([
            'command_queue_service' => 'db',
            'command_store_service' => 'discard',
        ]);

        /*
        $payload = rand(1,999);

        $command = new TestWithoutHandlerCommand($payload);
        */

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

        $command = new TestCommand($strParam1, $intParam2, $floatParam3, $clsParam4, $arrParam5);


        $queue->shouldReceive('append')->withAnyArgs();

        $result = $service->dispatchCommand($command);

        $loggerSpy->shouldHaveReceived('debug')->withArgs(['Fire command: ' . $command->getCommandName()]);

    }

    public function testQueue2RabbitAndStore2DiscardCommandsQueueService()
    {

        // putenv('COMMAND_QUEUE_SERVICE=rabbitmq');
        // putenv('COMMAND_STORE_SERVICE=discard');

        // Mock Logger
        $loggerSpy = Mockery::spy('Psr\Log\LoggerInterface');

        // Mock Queue
        $queue = Mockery::mock('Webravo\Persistence\Service\RabbitMQService');

        app()->instance('Psr\Log\LoggerInterface', $loggerSpy);
        app()->instance('Webravo\Persistence\Service\RabbitMQService', $queue);

        $queue->shouldReceive('createChannel')->withAnyArgs();
        $queue->shouldReceive('createQueue')->withAnyArgs()->andReturnValues([1]);
        $queue->shouldReceive('subscribeQueue')->withAnyArgs();
        // $queue->shouldReceive('bindQueue')->withAnyArgs();
        $queue->shouldReceive('getDefaultQueue')->withAnyArgs();
        $queue->shouldReceive('setDefaultQueue')->withAnyArgs();

        $service = new CommandsQueueService([
            'command_queue_service' => 'rabbitmq',
            'command_store_service' => 'discard',
        ]);

        $payload = rand(1,999);

        $command = new TestWithoutHandlerCommand($payload);
        $a_command = $command->toArray();
        $json_payload = json_encode($a_command );

        $queue->shouldReceive('publishMessage')->withArgs([ $json_payload, null, null, []]);

        $result = $service->dispatchCommand($command);

        $loggerSpy->shouldHaveReceived('debug')->withArgs(['Fire command: ' . $command->getCommandName()]);
    }

    public function testQueue2RabbitAndStore2DatastoreCommandsQueueService()
    {
        // putenv('COMMAND_QUEUE_SERVICE=rabbitmq');
        // putenv('COMMAND_STORE_SERVICE=datastore');

        // Mock Logger
        $loggerSpy = Mockery::spy('Psr\Log\LoggerInterface');

        // Mock Queue
        $queue = Mockery::mock('Webravo\Persistence\Service\RabbitMQService');

        // Mock Datastore
        $store = Mockery::spy('Webravo\Persistence\Datastore\Store\DataStoreCommandStore');

        app()->instance('Psr\Log\LoggerInterface', $loggerSpy);
        app()->instance('Webravo\Persistence\Service\RabbitMQService', $queue);
        app()->instance('Webravo\Persistence\Datastore\Store\DataStoreCommandStore', $store);

        $queue->shouldReceive('createChannel')->withAnyArgs();
        $queue->shouldReceive('createQueue')->withAnyArgs()->andReturnValues([1]);
        $queue->shouldReceive('subscribeQueue')->withAnyArgs();
        // $queue->shouldReceive('bindQueue')->withAnyArgs();
        $queue->shouldReceive('getDefaultQueue')->withAnyArgs();
        $queue->shouldReceive('setDefaultQueue')->withAnyArgs();

        $service = new CommandsQueueService([
            'command_queue_service' => 'rabbitmq',
            'command_store_service' => 'datastore',
        ]);

        $payload = rand(1,999);

        $command = new TestWithoutHandlerCommand($payload);
        $a_command = $command->toArray();
        $json_payload = json_encode($a_command );

        $queue->shouldReceive('publishMessage')->withArgs([ $json_payload, null, null, []]);

        $result = $service->dispatchCommand($command);

        $loggerSpy->shouldHaveReceived('debug')->withArgs(['Fire command: ' . $command->getCommandName()]);

        $store->shouldHaveReceived('append');
    }

    public function testQueue2DbAndStore2DbCommandsQueueService()
    {

        // putenv('COMMAND_QUEUE_SERVICE=db');
        // putenv('COMMAND_STORE_SERVICE=db');

        // Mock Logger
        $loggerSpy = Mockery::spy('Psr\Log\LoggerInterface');

        // Mock Queue
        // (EloquentJobStore cannot be spied !!)
        $queue = Mockery::mock('Webravo\Persistence\Eloquent\Store\EloquentJobStore');

        // Mock store
        $store = Mockery::spy('Webravo\Persistence\Eloquent\Store\EloquentCommandStore');

        app()->instance('Psr\Log\LoggerInterface', $loggerSpy);
        app()->instance('Webravo\Persistence\Eloquent\Store\EloquentJobStore', $queue);
        app()->instance('Webravo\Persistence\Eloquent\Store\EloquentCommandStore', $store);

        $queue->shouldReceive('createQueue')->withAnyArgs()->andReturnValues([1]);
        $queue->shouldReceive('bindQueue')->withAnyArgs();

        $service = new CommandsQueueService([
            'command_queue_service' => 'db',
            'command_store_service' => 'db',
        ]);

        $payload = rand(1,999);

        $command = new TestWithoutHandlerCommand($payload);
        $command->setQueueName('test-queue');
        $a_command = $command->toArray();
        $json_payload = json_encode($a_command);
        $queue_name = $command->getQueueName();
        $binding_key = $command->getBindingKey();
        $header = $command->getHeader();
        $queue->shouldReceive('append')->withArgs([$json_payload, $queue_name, $binding_key, $header]);

        $result = $service->dispatchCommand($command);

        $loggerSpy->shouldHaveReceived('debug')->withArgs(['Fire command: ' . $command->getCommandName()]);

        $store->shouldHaveReceived('append');
    }

    public function testQueue2SyncAndStore2DataStoreCommandsQueueService()
    {
        // putenv('COMMAND_QUEUE_SERVICE=sync');
        // putenv('COMMAND_STORE_SERVICE=datastore');

        // Mock Logger
        $loggerSpy = Mockery::spy('Psr\Log\LoggerInterface');

        // Mock Command Handler
        $handler = Mockery::spy(TestHandler::class);

        // Mock Datastore
        $store = Mockery::spy('Webravo\Persistence\Datastore\Store\DataStoreCommandStore');

        app()->instance('Psr\Log\LoggerInterface', $loggerSpy);
        app()->instance('tests\TestProject\Domain\Commands\TestHandler', $handler);
        app()->instance('Webravo\Persistence\Datastore\Store\DataStoreCommandStore', $store);

        $service = new CommandsQueueService([
            'command_queue_service' => 'sync',
            'command_store_service' => 'datastore',
        ]);

        /*
        $payload = rand(1,999);

        $command = new TestCommand($payload);
        */

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

        $command = new TestCommand($strParam1, $intParam2, $floatParam3, $clsParam4, $arrParam5);

        $handler->shouldReceive('handle')->withArgs([$command]);

        $result = $service->dispatchCommand($command);

        $loggerSpy->shouldHaveReceived('debug')->withArgs(['Fire command: ' . $command->getCommandName()]);

        $handler->shouldHaveReceived('handle');
        $store->shouldHaveReceived('append');

    }

    public function testSimulateRemoteDispatch()
    {
        // putenv('COMMAND_QUEUE_SERVICE=rabbitmq');
        // putenv('COMMAND_STORE_SERVICE=discard');
        // putenv('COMMAND_QUEUE=test-command-bus02');

        $service = new CommandsQueueService([
            'command_queue_service' => 'rabbitmq',
            'command_store_service' => 'discard',
            'command_queue' => 'test-command-bus02'
        ]);

        $payload = rand(1,999);
        $command = new TestWithoutHandlerCommand($payload);
        $result = $service->dispatchCommand($command);

        // Mock Command Handler
        $handler = Mockery::spy(TestHandler::class);
        app()->instance('tests\TestProject\Domain\Commands\TestWithoutHandlerHandler', $handler);

        // $handler->shouldReceive('handle')->withAnyArgs();

        // Create a second instance of QueueService to simulate a command receiver process
        $service2 = new CommandsQueueService([
            'command_queue_service' => 'rabbitmq',
            'command_store_service' => 'discard',
            'command_queue' => 'test-command-bus02'
        ]);
        $service2->processCommandQueue();

        $handler->shouldHaveReceived('handle');

    }

}