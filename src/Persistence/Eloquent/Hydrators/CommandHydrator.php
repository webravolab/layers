<?php
namespace Webravo\Persistence\Eloquent\Hydrators;

use Webravo\Common\Contracts\HydratorInterface;
use Webravo\Persistence\Eloquent\DataTable\CommandDataTable;

class CommandHydrator implements HydratorInterface {

    /**
     * Convert eloquent attributes to array
     * @param $eloquent_object
     * @return array                    // TODO declare return type when all implementors have been refactored
     */
    public function hydrateEloquent($object) {

        $data = [
            'id' => $object->id,
            'guid' => $object->guid,
            'command' => $object->command,
            'binding_key' => $object->binding_key,
            'queue_name' => $object->queue_name,
            'payload' => json_decode($object->payload, true),
            'header' => json_decode($object->header, true),
            'created_at' => $object->created_at,
        ];
        return $data;
    }

    /**
     * map (convert) from entity array to eloquent attributes array
     * @param $a_values
     * @return array
     */
    public function mapEloquent(array $a_values): array
    {
        $data = [
            'guid' => (isset($a_values['guid']) ? $a_values['guid'] : ''),
            'command' => (isset($a_values['command']) ? $a_values['command'] : ''),
            'binding_key' => (isset($a_values['binding_key']) ? $a_values['binding_key'] : ''),
            'queue_name' => (isset($a_values['queue_name']) ? $a_values['queue_name'] : null),
            'payload' => (isset($a_values['payload']) ? json_encode($a_values['payload']) : null),
            'header' => (isset($a_values['header']) ? json_encode($a_values['header']) : null),
            'created_at' => (isset($a_values['created_at']) ? $a_values['created_at'] : null),
        ];
        if (isset($a_values['id']) && $a_values['id'] > 0) {
            // Set ID only if exists and not empty
            $data['id'] = $a_values['id'];
        }
        return $data;
    }

    public function hydrateDatastore($datastore_object): array
    {
        // TODO: Implement hydrateDatastore() method.
    }

    public function mapDatastore(array $a_values): array
    {
        // TODO: Implement mapDatastore() method.
    }

}