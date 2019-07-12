<?php

namespace Webravo\Application\Event;

interface EventInterface {

    public function getType(): string;

    public function getClassName(): string;

    public function toArray(): array;

    public function fromArray(array $data);

    public static function buildFromArray(array $data): EventInterface;

    public function getSerializedPayload(): string;

}