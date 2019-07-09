<?php

namespace Webravo\Common\Entity;

interface EntityInterface
{
    public function getGuid();

    public function toArray(): array;

    public function fromArray(array $a_values);

    public static function buildFromArray(array $a_values);

}