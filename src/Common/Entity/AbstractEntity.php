<?php

namespace Webravo\Common\Entity;

use Webravo\Infrastructure\Library\DependencyBuilder;

abstract class AbstractEntity implements EntityInterface
{
    protected $guid;

    public function __construct()
    {
        $guidService = DependencyBuilder::resolve('Webravo\Infrastructure\Service\GuidServiceInterface');
        $this->guid = $guidService->generate()->getValue();
    }

    public function getGuid()
    {
        return $this->guid;
    }

    protected function setGuid($guid)
    {
        $this->guid = $guid;
    }

    /**
     * toArray() must be implemented by derived class
     * @return array
     */
    abstract public function toArray(): array;

    /**
     * fromArray() must be implemented by derive class
     * @param array $a_values
     * @return mixed
     */
    abstract public function fromArray(array $a_values);

    /**
     * Standard way to build an entity instance from an array of properties
     * (could be overridden to handle custom properties that need special handling)
     * @param array $a_values
     * @return mixed|AbstractEntity
     */
    public static function buildFromArray(array $a_values)
    {
        $instance = new static();
        $instance->fromArray($a_values);
        return $instance;
    }

}