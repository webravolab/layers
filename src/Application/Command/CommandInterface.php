<?php

namespace Webravo\Application\Command;

interface CommandInterface {

    public function getCommandName(): string;
    public function setCommandName($value);
    public function getBindingKey(): ?string;
    public function setBindingKey($value);
    public function getQueueName(): ?string;
    public function setQueueName($value);
    public function getHeader(): array;
    public function setHeader(array $value);
    public function toArray(): array;
    public function fromArray(array $data);
    public static function buildFromArray(array $data): CommandInterface;
    public function getSerializedPayload(): string;

}