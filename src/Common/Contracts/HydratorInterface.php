<?php

namespace Webravo\Common\Contracts;

interface HydratorInterface {

    /**
     * Convert eloquent attributes to array of properties
     * @param $eloquent_object
     * @return array                    // TODO declare return type when all implementors have been refactored
     */
    public function hydrate($eloquent_object): array;


    /**
     * map (convert) from array of entity properties to eloquent array of attributes
     * @param $a_values
     * @return array
     */
    public function map(array $a_values): array;
    
}