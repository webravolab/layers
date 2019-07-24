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

    public function dispatch(CommandInterface $command): ?CommandResponse {

        if (!is_null($this->commandStore)) {
            $this->commandStore->Append($command);
        }
        if (!is_null($this->next)) {
            return $this->next->dispatch($command);
        }
        return null;
    }
}
