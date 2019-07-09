<?php

use Webravo\Application\Command\CommandBusFactory;
use Webravo\Application\Command\GenericCommand;
use Webravo\Infrastructure\Library\Configuration;
use Webravo\Persistence\Eloquent\Store\EloquentEventStore;
use Webravo\Application\Event\EventBusDispatcher;
use Webravo\Application\Event\EventBucketBusMiddleware;
use Webravo\Persistence\Datastore\Store\DataStoreEventStore;

use Webravo\Persistence\Eloquent\Store\EloquentJobStore;
use Webravo\Persistence\Service\DBQueueService;
use Webravo\Application\Command\RemoteBusMiddleware;
use Webravo\Persistence\Service\RabbitMQService;

class CommandBusTest extends TestCase
{
    private $commandBusInternal;
    private $queueService;
    private $subscriberService;
    private $strategy = 'fanout';
    private $exchange_name = 'test-remote-command'; // -exchange';
    private $queue_name = 'test-remote-command'; // -queue';
    private $bind_name = 'test-remote-command-bind'; //-queue-bind';

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

        $commandRemoteDispatcher = new RemoteBusMiddleware(null, $this->queueService);

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
