<?php

namespace Webravo\Application\Event;

use DateTimeInterface;

interface EventInterface {

    public function getGuid(): string;

    public function setGuid(string $guid);

    public function getType(): string;

    public function setType(string $type);

    public function getOccurredAt(): ?DateTimeInterface;

    public function setOccurredAt($occurred_at);

    public function getPayload();

    public function setPayload($value);

    public function toArray(): array;

    public function fromArray(array $data);

    public static function buildFromArray(array $data): ?EventInterface;

    public function getSerializedPayload(): string;

    public function setSerializedPayload(string $payload_serialized): string;

    public function getSerializedEvent(): string;

    public static function buildFromSerializedEvent(string $event_serialized): ?EventInterface;

}