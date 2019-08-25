<?php
namespace Webravo\Application\Command;

use Webravo\Application\Event\EventInterface;

interface CommandResponseInterface {

    public static function withValue($value, iterable $events = []): CommandResponseInterface;

    public function addEvent(EventInterface $event): void;

    public function hasEvents(): bool;

    public function allEvents(): iterable;

    public function getValue();
}