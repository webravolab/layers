<?php

use Webravo\Application\Command\CommandBusFactory;
use Webravo\Application\Command\GenericCommand;
use Webravo\Infrastructure\Library\Configuration;
use Webravo\Persistence\Eloquent\Store\EloquentJobStore;
use Webravo\Persistence\Service\DBQueueService;
use Webravo\Persistence\Service\NullQueueService;
use Webravo\Application\Command\CommandRemoteBusMiddleware;
use Webravo\Persistence\Service\RabbitMQService;
use Webravo\Persistence\Service\NullLoggerService;

class CommandBusTest extends TestCase
{
    private $commandBusInternal;
    private $queueService;
    private $subscriberService;
    private $strategy = 'fanout';
    private $exchange_name = 'test-remote-command'; // -exchange';
    private $queue_name = 'test-remote-command'; // -queue';
    private $bind_name = 'test-remote-command-bind'; //-queue-bind';

    public function testCommandBusFactory()
    {
        $this->queueService = new NullQueueService();
        $loggerService = new NullLoggerService();

        $commandBus = CommandBusFactory::build($this->queueService, $loggerService);

        $strParam1 = 'This is a command without handler test 1';

        $command = new \tests\Commands\TestWithoutHandlerCommand($strParam1);

        // This dispatch must return null, because there is no local handler and no remote command bus
        $response = $commandBus->dispatch($command);

        self::assertNull($response, "Command dispatch response must be Null");
    }

    public function testCommandBusFactoryWithDummyHandler()
    {
        // Add a dummy command handler to satisfy the local command dispatcher
        /*
        app()->bind('tests\Commands\TestWithoutHandlerHandler', function ($app) {
            return new tests\Commands\DummyHandler();
        });
        */

        app()->bind('tests\Commands\TestWithoutHandlerHandler', 'tests\Commands\DummyHandler');

        $this->queueService = new NullQueueService();
        $loggerService = new NullLoggerService();

        $commandBus = CommandBusFactory::build($this->queueService, $loggerService);

        $strParam1 = 'This is a command without handler test 2';

        $command = new \tests\Commands\TestWithoutHandlerCommand($strParam1);

        // This dispatch must return null, because there is no local handler and no remote command bus
        $response = $commandBus->dispatch($command);

        self::assertEquals($response->getValue(), "dummy says ok", "Command response is invalid");
    }

    public function testCommandRemoteBusMiddleware_DB()
    {
        // Test using DB Queue
        $jobQueueService = new EloquentJobStore();
        $this->queueService = new DBQueueService($jobQueueService);
        $this->subscriberService = new DBQueueService($jobQueueService);
        $this->abstractRemoteBusMiddlewareTest();

    }

    public function testCommandRemoteBusMiddleware_Rabbit()
    {
        // Test using RabbitMQ Queue
        $this->queueService = new RabbitMQService();
        $this->subscriberService = new RabbitMQService();
        $this->abstractRemoteBusMiddlewareTest();
    }

    public function abstractRemoteBusMiddlewareTest()
    {
        $this->queueService->deleteChannel($this->exchange_name);

        $this->queueService->createChannel($this->strategy, $this->exchange_name);
        $this->queueService->setDefaultQueue($this->queue_name);
        $this->queueService->createQueue($this->queue_name);

        $commandRemoteDispatcher = new CommandRemoteBusMiddleware(null, $this->queueService);

        $this->subscriberService->createChannel($this->strategy, $this->exchange_name);
        $this->subscriberService->createQueue($this->bind_name);
        $this->subscriberService->subscribeQueue($this->bind_name, $this->exchange_name);

        $this->commandBusInternal = CommandBusFactory::build(null, null);

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
        // $command->setQueueName($this->queue_name);
        $command->setBindingKey(null);
        $command->setHeader([
            'from' => 'CommandBustest1',
            'to' => 'CommandBusTest2'
        ]);

        $commandRemoteDispatcher->dispatch($command);

        $this->subscriberService->processSingleMessage($this->bind_name, function ($message) {
            $response = $this->CommandCallback($message);
            $this->assertEquals('ok', $response->getValue(), 'Command response does not return ok');
            $this->subscriberService->messageAcknowledge($message);
            $this->queueService->deleteChannel($this->exchange_name);
        });

        $this->subscriberService->close();
    }

    public function CommandCallback($message)
    {
        $commandPayload = json_decode($message->body, true);
        $genericCommmand = GenericCommand::buildFromArray($commandPayload);
        $response = $this->commandBusInternal->dispatch($genericCommmand);
        return $response;
    }
}
