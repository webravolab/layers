<?php

namespace Webravo\Application\Event;

use Webravo\Application\Event\EventInterface;

interface EventHandlerInterface {

    function handle(EventInterface $event): void;

    static function listenTo(): string;
}