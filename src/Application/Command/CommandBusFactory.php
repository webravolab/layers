<?php
namespace Webravo\Application\Command;

use Webravo\Infrastructure\Service\QueueServiceInterface;
use Webravo\Infrastructure\Library\DependencyBuilder;
use Psr\Log\LoggerInterface;

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
            new CommandRemoteBusMiddleware(new CommandBusDispatcher(), $queueService), $loggerService
        );
    }
}
