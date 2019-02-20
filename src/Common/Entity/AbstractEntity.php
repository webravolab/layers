<?php

namespace Webravo\Common\Entity;

use Webravo\Common\Entity\EntityInterface;
use Webravo\Infrastructure\Service\GuidServiceInterface;
use Webravo\Infrastructure\Library\DependencyBuilder;

abstract class AbstractEntity implements EntityInterface
{
    protected $guid;

    public function __construct() {
        $guidService = DependencyBuilder::resolve('Webravo\Infrastructure\Service\GuidServiceInterface');
        $this->guid = $guidService->generate()->getValue();
    }

    public function getGuid() {
        return $this->guid;
    }

    abstract public function toArray(): array;

    abstract public function fromArray(array $a_values);

    public static function buildFromArray(array $a_values): EntityInterface
    {
        $instance = new static();
        $instance->fromArray($a_values);
        return $instance;
    }

}