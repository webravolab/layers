<?php
namespace Webravo\Persistence\Hydrators;

use Webravo\Common\Entity\EventEntity;
use Webravo\Common\Contracts\HydratorInterface;
use Webravo\Persistence\Eloquent\DataTable\EventDataTable;
use DateTime;

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
            // Must convert DateTimeImmutable to DateTime for BigQuery compatibility
            'occurred_at' => new DateTime($a_values['occurred_at']->format(DATE_RFC3339_EXTENDED)),
            'class_name' => $a_values['class_name'] ?? '',
            'payload' => $a_values['payload'],
            'guid' => $a_values['guid'],
        ];
        return $data;
    }

    public function getSchema(): array {
        return ['fields' =>
            [
                [
                    'name' => 'guid',
                    'type' => 'string',
                    'mode' => 'required',
                ],
                [
                    'name' => 'type',
                    'type' => 'string',
                    'mode' => 'required',
                ],
                [
                    'name' => 'class_name',
                    'type' => 'string',
                    'mode' => 'nullable',
                ],
                [
                    'name' => 'occurred_at',
                    'type' => 'datetime',
                ],
                [
                    'name' => 'payload',
                    'type' => 'string',
                ],
            ]
        ];
    }
}