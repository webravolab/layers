<?php
namespace Webravo\Application\Command;

use Webravo\Common\Contracts\DomainEventInterface;

interface CommandResponseInterface {

    public static function withValue($value): CommandResponseInterface;

    public function addEvent(DomainEventInterface $event): void;

    public function hasEvents(): bool;

    public function allEvents(): array;

    public function getValue();
}