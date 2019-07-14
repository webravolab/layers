<?php
namespace Webravo\Application\Command;

use Webravo\Application\Event\EventInterface;
use Webravo\Application\Command\CommandResponseInterface;

class CommandResponse implements CommandResponseInterface {

    private $value;
    private $events = Array();

    public function __construct($value) {
        $this->value = $value;
    }

    public static function withValue($value): CommandResponseInterface {
        return new self($value);
    }

    public function addEvent(EventInterface $event): void {
        $this->events[] = $event;
    }

    public function hasEvents(): bool {
        return (count($this->events) > 0);
    }

    public function allEvents(): array {
        return $this->events;
    }

    public function getValue() {
        return $this->value;
    }
}