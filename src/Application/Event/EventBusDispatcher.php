<?php
namespace Webravo\Application\Event;

use Webravo\Common\Contracts\DomainEventInterface;
use Webravo\Application\Event\EventHandlerInterface;
use Webravo\Infrastructure\Library\DependencyBuilder;
use ReflectionClass;


class EventBusDispatcher implements EventBusMiddlewareInterface
{
    private $handlers = [];
    private static $instance = null;

    public static function instance()
    {
        if (null === static::$instance) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    public function subscribe($handler):void {
        $reflect = new ReflectionClass($handler);
        if($reflect->implementsInterface('Webravo\Application\Event\EventHandlerInterface')) {
            $event_name = call_user_func(array($handler, 'listenTo'));
            if (isset($this->handlers[$event_name])) {
                foreach($this->handlers[$event_name] as $subscribed_handler) {
                    if ($subscribed_handler === $handler) {
                        // Already subscribed
                        return;
                    } 
                }
            }
            $this->handlers[$event_name][] = $handler;
        }
    }

    public function dispatch(EventInterface $event):void  {
        $event_class = get_class($event);
        if (isset($this->handlers[$event_class])) {
            $handlers = $this->handlers[$event_class];
            foreach($handlers as $handler_class) {
                $class = DependencyBuilder::resolve($handler_class);
                call_user_func(array($class, 'handle'), $event);
            }
        }
    }
}