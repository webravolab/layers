<?php

namespace Webravo\Application\Command;

use Webravo\Application\Exception\CommandException;
use Webravo\Infrastructure\Library\DependencyBuilder;

class CommandBusDispatcher implements CommandBusMiddlewareInterface {

    private $next;

    public function __construct(?CommandBusMiddlewareInterface $next) {
        $this->next = $next;
    }

    public function dispatch(CommandInterface $command): ?CommandResponse {
        $commandClass = get_class($command);
        $handlerClass = $this->getHandler($command);
        // First... attempt to build the handler class by the DependencyBuilder ...
        // ... to inject any interface referenced in class constructor...
        $handlerInstance = DependencyBuilder::resolve($handlerClass);
        if ($handlerInstance === null) {
            // Second... try to instantiate the class directly
            if (class_exists($handlerClass)) {
                $handlerInstance = new $handlerClass;
            }
            else {
                // Third: dispatch the command to the next component in the bus
                if (!is_null($this->next)) {
                    // If no local command handler is available, forward to the next level in the bus
                    return $this->next->dispatch($command);
                }
                else {
                    // No local handler and no more levels in the bus
                    throw new CommandException('Handler not found for command: ' . $commandClass, 101);
                }
            }
        }
        return $handlerInstance->handle($command);
    }


    public function getHandler(CommandInterface $command)
    {
        $command_class_name = get_class($command);
        if (substr($command_class_name,-7) == 'Command') {
            $handler_class_name = substr($command_class_name,0,-7) . 'Handler';
            return $handler_class_name;
        }
        throw new CommandException('Command ' . $command_class_name. ' has invalid name', 102);
    }
}
