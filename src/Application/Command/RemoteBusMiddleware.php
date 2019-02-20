<?php
namespace Webravo\Application\Command;

use Webravo\Application\Command\CommandResponse;
use Webravo\Infrastructure\Library\Configuration;
use Webravo\Infrastructure\Service\QueueServiceInterface;

class RemoteBusMiddleware implements CommandBusMiddlewareInterface {

    private $next;
    private $queueService;

    public function __construct(CommandBusMiddlewareInterface $next ,  ?QueueServiceInterface $queueService) {
        $this->next = $next;
        $this->queueService = $queueService;
        if ($this->queueService && empty($this->queueService->getDefaultQueue())) {
            $command_queue = Configuration::get('COMMAND_QUEUE',null, 'command-bus');
            $this->queueService->setDefaultQueue($command_queue);
        }
    }

    public function dispatch(CommandInterface $command): ?CommandResponse {
        if (is_null($this->queueService)) {
            // If no remote command queue is available, try to find a local handler
            $response = $this->next->dispatch($command);
            return $response;
        }
        // Dispatch to the remote command queue
        $payload = $command->toArray();
        $json_payload = json_encode($payload);
        $queue_name = $command->getQueueName();
        $binding_key = $command->getBindingKey();
        $header = $command->getHeader();
        $this->queueService->publishMessage($json_payload, $queue_name, $binding_key, $header);
        return null;
    }
}
