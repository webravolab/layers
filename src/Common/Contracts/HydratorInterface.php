<?php

namespace Webravo\Common\Contracts;

interface HydratorInterface {

    /**
     * Convert Eloquent attributes to array of properties
     * @param $eloquent_object
     * @return array                    // TODO declare return type when all implementors have been refactored
     */
    public function hydrateEloquent($eloquent_object): array;

    /**
     * Convert Datastore attributes to array of properties
     * @param $eloquent_object
     * @return array                    // TODO declare return type when all implementors have been refactored
     */
    public function hydrateDatastore($datastore_object): array;


    /**
     * map (convert) from array of entity properties to Eloquent array of attributes
     * @param $a_values
     * @return array
     */
    public function mapEloquent(array $a_values): array;

    /**
     * map (convert) from array of entity properties to Datastore array of attributes
     * @param $a_values
     * @return array
     */
    public function mapDatastore(array $a_values): array;

}