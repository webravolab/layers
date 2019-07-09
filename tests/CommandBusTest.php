<?php

use Webravo\Application\Command\CommandBusFactory;
use Webravo\Application\Command\GenericCommand;
use Webravo\Infrastructure\Library\Configuration;
use Webravo\Persistence\Eloquent\Store\EloquentEventStore;
use Webravo\Application\Event\EventBusDispatcher;
use Webravo\Application\Event\EventStoreBusMiddleware;
use Webravo\Persistence\Datastore\Store\DataStoreEventStore;

use Webravo\Persistence\Eloquent\Store\EloquentJobStore;
use Webravo\Persistence\Service\DBQueueService;
use Webravo\Application\Command\RemoteBusMiddleware;

class CommandBusTest extends TestCase
{

    private $commandBusInternal;

    public function testEventRemoteBusMiddleware()
    {
        $queue_name = 'test-remote-command';
        $jobQueueService = new EloquentJobStore();
        $queueService = new DBQueueService($jobQueueService);
        $queueService->createChannel('', $queue_name);
        $queueService->setDefaultQueue($queue_name);
        $queueService->createQueue($queue_name);

        $commandRemoteDispatcher = new RemoteBusMiddleware(null, $queueService);

        $subscriberService = new DBQueueService($jobQueueService);
        $subscriberService->subscribeQueue($queue_name);

        $this->commandBusInternal = CommandBusFactory::build(null, null);

        /*
        $callback = function ($message) use ($subscriberService) {
            echo "Message received: " . $message->body . "\n";
            $subscriberService->messageAcknowledge($message);
        };
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

        $command = new \tests\Commands\TestCommand($strParam1, $intParam2, $floatParam3, $clsParam4, $arrParam5);
        $command->setQueueName($queue_name);
        $command->setBindingKey(null);
        $command->setHeader([
            'from' => 'CommandBustest1',
            'to' => 'CommandBusTest2'
        ]);

        $commandRemoteDispatcher->dispatch($command);

        $subscriberService->processSingleMessage($queue_name, function ($message) {
            $response = $this->CommandCallback($message);
            $this->assertEquals('ok', $response->getValue(), 'Command response does not return ok');
        });
        $subscriberService->close();

        // $message1 = $subscriberService->getSingleMessage($queue_name);

        // $this->assertNotNull($message1, 'Message 11 must not be null');

    }

    public function CommandCallback($message)
    {
        $commandPayload = json_decode($message->body, true);
        $genericCommmand = GenericCommand::buildFromArray($commandPayload);
        $response = $this->commandBusInternal->dispatch($genericCommmand);
        return $response;
    }
}
