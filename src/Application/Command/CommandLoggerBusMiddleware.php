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

    public function dispatch(CommandInterface $command): ?CommandResponse {

        if (!is_null($this->loggerService)) {
            $this->loggerService->debug('Fire command: ' . $command->getCommandName());
        }
        if (!is_null($this->next)) {
            return $this->next->dispatch($command);
        }

        /*
        if (!is_null($this->loggerService)) {
            $this->loggerService->debug('After command: ' . $command->getCommandName());
        }
        */
        return null;
    }
}
