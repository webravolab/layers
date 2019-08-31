<?php

namespace Webravo\Application\Command;

use Webravo\Application\Exception\CommandException;
use Webravo\Infrastructure\Library\DependencyBuilder;

class CommandBusDispatcher implements CommandBusMiddlewareInterface {

    private $next;
    private $dispatchMapper = [];

    public function __construct(?CommandBusMiddlewareInterface $next) {
        $this->next = $next;
    }

    public function subscribeHandlerMapper(array $mapper, $handler_instance): void
    {
        try {
            foreach($mapper as $command_name)
            {
                if (!isset($this->dispatchMapper[$command_name])) {
                    $this->dispatchMapper[$command_name] = $handler_instance;
                }
            }
        }
        catch (\Exception $e) {
            throw new CommandException('[CommandBusDispatcher][subscribeHandlerMapper] Cannot register mapper:' .  $e->getMessage());
        }

    }

    public function dispatch(CommandInterface $command): ?CommandResponse
    {
        if (($command_response = $this->dispatchToMapper($command)) instanceof CommandResponse) {
            return $command_response;
        }
        $commandClass = get_class($command);
        $handlerClass = $this->getHandler($command);
        // First... attempt to build the handler class by the DependencyBuilder ...
        // ... to inject any interface referenced in class constructor...
        $handlerInstance = DependencyBuilder::resolve($handlerClass);
        if ($handlerInstance === null) {
            // Second... try to instantiate the class directly
            if (class_exists($handlerClass)) {
                $handlerInstance = new $handlerClass;
            } else {
                // Third: dispatch the command to the next component in the bus
                if (!is_null($this->next)) {
                    // If no local command handler is available, forward to the next level in the bus
                    return $this->next->dispatch($command);
                } else {
                    // No local handler and no more levels in the bus
                    throw new CommandException('Handler not found for command: ' . $commandClass, 101);
                }
            }
        }
        return $handlerInstance->handle($command);
    }

    private function dispatchToMapper(CommandInterface $command): ?CommandResponse
    {
        try {
            $command_name = get_class($command);
            if (!isset($this->dispatchMapper[$command_name])) {
                // Try only with command basename
                $command_name = basename($command_name);
            }
            if (isset($this->dispatchMapper[$command_name])) {
                $handler_class = $this->dispatchMapper[$command_name];
                if (is_string($handler_class)) {
                    // Get instance by name
                    if (class_exists($handler_class)) {
                        $handler_method = 'when' . $command_name;
                        return $handler_class::$handler_method($command);
                    }
                }
                else {
                    $handler_method = 'when' . $command_name;
                    if (method_exists($handler_class, $handler_method)) {
                        $response = $handler_class->$handler_method($command);
                        return $response;
                    }
                }
            }
        }
        catch(\Exception $e) {
            throw new CommandException('[CommandBusDispatcher][dispatchToMapper] error dispatching command: ' . $command_name . ' - Error: ' . $e->getMessage());
        }
        return null;
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
