<?php
namespace Webravo\Application\Command;

use Webravo\Application\Command\CommandResponse;
use Psr\Log\LoggerInterface;
use Webravo\Infrastructure\Repository\CommandStoreInterface;

class CommandBucketBusMiddleware implements CommandBusMiddlewareInterface {

    private $next;
    private $commandStore;

    public function __construct(CommandBusMiddlewareInterface $next,  ?CommandStoreInterface $commandStore = null) {
        $this->next = $next;
        $this->commandStore = $commandStore;
    }

    public function dispatch(CommandInterface $command): ?CommandResponse {

        if (!is_null($this->commandStore)) {
            $this->commandStore->Append($command);
        }

        $response = $this->next->dispatch($command);

        return $response;
    }
}
