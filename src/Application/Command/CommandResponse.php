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

    public static function withValue($value, iterable $events = []): CommandResponseInterface {
        $response = new self($value);
        $response->events = $events;
        return $response;
    }

    public function addEvent(EventInterface $event): void {
        $this->events[] = $event;
    }

    public function hasEvents(): bool {
        return (count($this->events) > 0);
    }

    public function allEvents(): iterable {
        return $this->events;
    }

    public function getValue() {
        return $this->value;
    }
}