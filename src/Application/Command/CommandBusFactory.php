<?php
namespace Webravo\Application\Command;

use Webravo\Infrastructure\Service\QueueServiceInterface;
use Webravo\Infrastructure\Library\DependencyBuilder;
use Psr\Log\LoggerInterface;

/**
 * Class CommandBusFactory
 *
 * Create a basic Command Bus including 3 middlewares, a Logger, a Local Command Dispatcher, a Remote Command Queue
 *
 * If a command handler is found locally (by class resolver) the local handler is invoked ...
 * ... otherwise the command is sent to the remote queue for remote execution
 *
 * @package Webravo\Application\Command
 */
class CommandBusFactory {

    private static $instance = null;

    public static function instance(QueueServiceInterface $queueService = null, LoggerInterface $loggerService = null)
    {
        if (null === static::$instance) {
            if (!$queueService) {
                $queueService = DependencyBuilder::resolve('Webravo\Infrastructure\Service\QueueServiceInterface');
            }
            if (!$loggerService) {
                $loggerService = DependencyBuilder::resolve('Psr\Log\LoggerInterface');
            }
            static::$instance = static::Build($queueService, $loggerService);
        }
        return static::$instance;
    }

    // Build Command bus stack
    static function Build(QueueServiceInterface $queueService = null, LoggerInterface $loggerService = null): CommandBusMiddlewareInterface {
        return new CommandLoggerBusMiddleware(
            new CommandBusDispatcher(
                new CommandRemoteBusMiddleware(null, $queueService)
            ), $loggerService
        );
    }
}
