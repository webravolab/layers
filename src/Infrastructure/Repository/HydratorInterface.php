<?php

namespace Webravo\Infrastructure\Repository;

interface HydratorInterface {

    /**
     * Convert eloquent attributes to array
     * @param $eloquent_object
     * @return array                    // TODO declare return type when all implementors have been refactored
     */
    public function Hydrate($eloquent_object);


    /**
     * Map (convert) from entity array to eloquent attributes array
     * @param $a_values
     * @return array
     */
    public function Map(array $a_values): array;
    

    // TODO - to be deleted
    // public function Extract($object);

}