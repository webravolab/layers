<?php

namespace Webravo\Application\Command;

use Webravo\Application\Exception\CommandException;
use Webravo\Infrastructure\Library\DependencyBuilder;

class CommandBusDispatcher implements CommandBusMiddlewareInterface {

    public function dispatch(CommandInterface $command): CommandResponse {
        $commandClass = get_class($command);
        $handlerClass = $this->getHandler($command);
        $handler = DependencyBuilder::resolve($handlerClass);
        if ($handler == null) {
            throw new CommandException('Handler for command ' . $commandClass . ' not found', 101);
        }
        return $handler->handle($command);
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
