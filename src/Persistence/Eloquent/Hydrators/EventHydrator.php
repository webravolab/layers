<?php
namespace Webravo\Persistence\Eloquent\Hydrators;

use Webravo\Common\Entity\DataStoreEventEntity;
use Webravo\Common\Contracts\HydratorInterface;
use Webravo\Persistence\Eloquent\DataTable\EventDataTable;

class EventHydrator implements HydratorInterface {


    /**
     * Convert eloquent attributes to array
     * (handle de-serialization of event payload)
     * @param $eloquent_object
     * @return array                    // TODO declare return type when all implementors have been refactored
     */
    public function hydrateEloquent($object): array
    {
        $data = [
            'id' => $object->id,
            'guid' => $object->guid,
            'type' => $object->event_type,
            'occurred_at' => $object->occurred_at,
            'payload' => json_decode($object->payload, true),
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
            'event_type' => (isset($a_values['type']) ? $a_values['type'] : ''),
            'occurred_at' => (isset($a_values['occurred_at']) ? $a_values['occurred_at'] : null),
            'payload' => (isset($a_values['payload']) ? $a_values['payload'] : null),
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

    /**
     * Extract data from Event instance and return as raw data array
     * (handle serialization of event payload)
     * @param DataStoreEventEntity $event
     * @return array
     * @throws \Exception
     */
    public function Extract(DataStoreEventEntity $eventEntity) {
        $data = [
            'guid' => $eventEntity->getGuid(),
            'event_type' => $eventEntity->getType(),
            'occurred_at' => $eventEntity->getOccurredAt(),
            'payload' => $eventEntity->getSerializedPayload()
        ];
        return $data;

    }
}