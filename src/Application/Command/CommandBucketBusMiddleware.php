<?php
namespace Webravo\Application\Command;

use Webravo\Application\Command\CommandResponse;
use Psr\Log\LoggerInterface;
use Webravo\Infrastructure\Repository\CommandRepositoryInterface;

class CommandBucketBusMiddleware implements CommandBusMiddlewareInterface {

    private $next;
    private $commandStore;

    public function __construct(?CommandBusMiddlewareInterface $next,  ?CommandRepositoryInterface $commandStore = null) {
        $this->next = $next;
        $this->commandStore = $commandStore;
    }

    public function subscribeHandlerMapper(array $mapper, $handler_instance): void
    {
        if (!is_null($this->next)) {
            // Invoke next stack level subscriber
            $this->next->subscribeHandlerMapper($mapper, $handler_instance);
        }
    }

    public function dispatch(CommandInterface $command): ?CommandResponse {

        if (!is_null($this->commandStore)) {
            $this->commandStore->append($command);
        }
        if (!is_null($this->next)) {
            return $this->next->dispatch($command);
        }
        return null;
    }
}
