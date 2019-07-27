<?php
namespace Webravo\Common\Contracts;

use Webravo\Application\Event\EventInterface;

interface EventsQueueServiceInterface {

    public function dispatchEvent(EventInterface $event): void;

    public function registerHandler($handler): void;

    public function processQueueEvents();

}
