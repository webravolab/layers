<?php
namespace Webravo\Application\Command;

use Webravo\Application\Command\CommandResponse;
use Webravo\Infrastructure\Library\Configuration;
use Webravo\Infrastructure\Service\QueueServiceInterface;

class CommandRemoteBusMiddleware implements CommandBusMiddlewareInterface {

    private $next;
    private $queueService;

    public function __construct(?CommandBusMiddlewareInterface $next ,  ?QueueServiceInterface $queueService) {
        $this->next = $next;
        $this->queueService = $queueService;
        if ($this->queueService && empty($this->queueService->getDefaultQueue())) {
            $command_queue = Configuration::get('COMMAND_QUEUE',null, 'command-bus');
            $this->queueService->setDefaultQueue($command_queue);
        }
    }

    public function subscribeHandlerMapper(array $mapper, $handler_instance): void
    {
        if (!is_null($this->next)) {
            // Invoke next stack level subscriber
            $this->next->subscribeHandlerMapper($mapper, $handler_instance);
        }
    }

    public function dispatch(CommandInterface $command): ?CommandResponse {
        if (!is_null($this->queueService)) {
            // Dispatch to the remote command queue
            $payload = $command->toArray();
            $json_payload = json_encode($payload);
            $queue_name = $command->getQueueName();
            $binding_key = $command->getBindingKey();
            $header = $command->getHeader();
            $this->queueService->publishMessage($json_payload, $queue_name, $binding_key, $header);
            return null;
        }
        if (!is_null($this->next)) {
            // If no remote command queue is available, forward to the next level in the bus
            return $this->next->dispatch($command);
        }
        return null;
    }
}
