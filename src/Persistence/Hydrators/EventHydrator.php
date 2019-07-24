<?php
namespace Webravo\Persistence\Hydrators;

use Webravo\Common\Entity\EventEntity;
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
        $data = [
            'guid' => $datastore_object['guid'],
            'type' => $datastore_object['type'],
            'occurred_at' => $datastore_object['occurred_at'],
            'payload' => json_decode($datastore_object['payload'], true),
            'class_name' => $datastore_object['class_name'] ?? '',
        ];
        return $data;
    }

    public function mapDatastore(array $a_values): array
    {
        $data = [
            'type' => $a_values['type'],
            'occurred_at' => $a_values['occurred_at'],
            'class_name' => $a_values['class_name'] ?? '',
            'payload' => $a_values['payload'],
            'guid' => $a_values['guid'],
        ];
        return $data;
    }

    /**
     * Extract data from Event instance and return as raw data array
     * (handle serialization of event payload)
     * @param EventEntity $event
     * @return array
     * @throws \Exception
     */
    /*
    public function Extract(EventEntity $eventEntity) {
        $data = [
            'guid' => $eventEntity->getGuid(),
            'event_type' => $eventEntity->getType(),
            'occurred_at' => $eventEntity->getOccurredAt(),
            'payload' => $eventEntity->getSerializedPayload()
        ];
        return $data;
    }
    */
}