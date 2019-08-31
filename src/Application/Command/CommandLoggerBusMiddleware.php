<?php
namespace Webravo\Application\Command;

use Webravo\Application\Command\CommandResponse;
use Psr\Log\LoggerInterface;

class CommandLoggerBusMiddleware implements CommandBusMiddlewareInterface {

    private $next;
    private $loggerService;

    public function __construct(?CommandBusMiddlewareInterface $next,  ?LoggerInterface $loggerService = null) {
        $this->next = $next;
        $this->loggerService = $loggerService;
    }

    public function subscribeHandlerMapper(array $mapper, $handler_instance): void
    {
        if (!is_null($this->next)) {
            // Invoke next stack level subscriber
            $this->next->subscribeHandlerMapper($mapper, $handler_instance);
        }
    }

    public function dispatch(CommandInterface $command): ?CommandResponse {

        if (!is_null($this->loggerService)) {
            $this->loggerService->debug('Fire command: ' . $command->getCommandName());
        }
        if (!is_null($this->next)) {
            return $this->next->dispatch($command);
        }
        return null;
    }
}
