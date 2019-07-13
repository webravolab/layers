<?php

namespace Webravo\Common\Entity;

interface EntityInterface
{
    /**
     * Retrieve entity by it's guid
     * @return mixed
     */
    public function getGuid();

    /**
     * Convert entity properties to a key => value Array
     * @return array
     */
    public function toArray(): array;


    /**
     * Fill entity properties from a key => value array
     * @param array $a_values
     * @return mixed
     */
    public function fromArray(array $a_values);

    /**
     * Create a new instance of entity given a key => value array to fill properties
     * @param array $a_values
     * @return mixed
     */
    public static function buildFromArray(array $a_values);

}