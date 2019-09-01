<?php
namespace Webravo\Persistence\Hydrators;

use Webravo\Common\Contracts\HydratorInterface;
use DateTime;

class EventStreamHydrator implements HydratorInterface {


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
            'event_type' => $object->event,
            'aggregate_type' => $object->aggregate_type,
            'aggregate_id' => $object->aggregate_id,
            'occurred_at' => $object->occurred_at,
            'payload' => json_decode($object->payload, true),
        ];
        return $data;    }

    /**
     * map (convert) from entity array to eloquent attributes array
     * @param $a_values
     * @return array
     */
    public function mapEloquent(array $a_values): array
    {
        $data = [
            'aggregate_type' => $a_values['aggregate_type'],
            'aggregate_id' => $a_values['aggregate_id'],
            'event' => $a_values['event_type'],
            'version' => $a_values['version'],
            'payload' => $a_values['payload'],
            'occurred_at' => $a_values['occurred_at'],
            'guid' => $a_values['guid'],
        ];
        return $data;
    }

    public function hydrateDatastore($datastore_object): array
    {
        $payload = json_decode($datastore_object['payload'], true);
        $event_type = $datastore_object['event'] ?? '';

        $data = [
            'guid' => $datastore_object['guid'],
            'aggregate_type' => $datastore_object['aggregate_type'],
            'aggregate_id' => $datastore_object['aggregate_id'],
            'event_type' => $event_type,
            'version' => $datastore_object['version'],
            'occurred_at' => $datastore_object['occurred_at'],
            'payload' => $payload,
            'class_name' => $payload['class_name'] ?? $event_type,
        ];
        return $data;
    }

    public function mapDatastore(array $a_values): array
    {
        $data = [
            'aggregate_type' => $a_values['aggregate_type'],
            'aggregate_id' => $a_values['aggregate_id'],
            'event' => $a_values['event_type'],
            'version' => $a_values['version'],
            'payload' => $a_values['payload'],
            // Must convert DateTimeImmutable to DateTime for BigQuery compatibility
            'occurred_at' => new DateTime($a_values['occurred_at']->format(DATE_RFC3339_EXTENDED)),
            'guid' => $a_values['guid'],
        ];
        return $data;
    }

    /**
     * Used by BigQuery to create entity table
     * @return array
     */
    public function getSchema(): array {
        return ['fields' =>
            [
                [
                    'name' => 'guid',
                    'type' => 'string',
                    'mode' => 'required',
                ],
                [
                    'name' => 'aggregate_type',
                    'type' => 'string',
                    'mode' => 'required',
                ],
                [
                    'name' => 'aggregate_id',
                    'type' => 'string',
                    'mode' => 'required',
                ],
                [
                    'name' => 'event_type',
                    'type' => 'string',
                    'mode' => 'required',
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