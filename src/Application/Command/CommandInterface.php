<?php

namespace Webravo\Application\Command;

use DateTimeInterface;

interface CommandInterface {

    public function getCommandName(): string;
    public function setCommandName($value);
    public function getBindingKey(): ?string;
    public function setBindingKey($value);
    public function getQueueName(): ?string;
    public function setQueueName($value);
    public function getHeader(): array;
    public function setHeader(array $value);
    public function getCreatedAt(): ?DateTimeInterface;
    public function setCreatedAt($created_at);
    public function toArray(): array;
    public function fromArray(array $data);
    public static function buildFromArray(array $data);
    // public function getSerializedPayload(): string;
    public function getSerializedCommand(): string;
    public static function buildFromSerializedCommand(string $command_serialized): ?CommandInterface;

}