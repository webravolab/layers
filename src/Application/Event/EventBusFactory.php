<?php
namespace Webravo\Application\Event;

use Webravo\Infrastructure\Service\QueueServiceInterface;
use Webravo\Application\Event\EventBusDispatcher;
use Webravo\Infrastructure\Library\DependencyBuilder;
use Psr\Log\LoggerInterface;

/**
 * Class EventBusFactory
 * Create a basic Event Bus including 3 middlewares, a Queue, a Logger and a Local Dispatcher
 * @package Webravo\Application\Event
 */
class EventBusFactory {

    private static $instance = null;

    public static function instance(QueueServiceInterface $queueService = null, LoggerInterface $loggerService = null)
    {
        if (null === static::$instance || !is_null($queueService) || !is_null($loggerService)) {
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

    // Build Event bus stack
    static function Build(QueueServiceInterface $queueService = null, LoggerInterface $loggerService = null): EventBusMiddlewareInterface {
        return new EventLoggerBusMiddleware(
            new EventRemoteBusMiddleware(EventBusDispatcher::instance(), $queueService), $loggerService
        );
    }
}
