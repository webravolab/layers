<?php
namespace Webravo\Persistence\Eloquent\Hydrators;

use Webravo\Infrastructure\Repository\HydratorInterface;
use Webravo\Persistence\Eloquent\DataTable\EventDataTable;

class EventHydrator implements HydratorInterface {


    /**
     * Convert eloquent attributes to array
     * @param $eloquent_object
     * @return array                    // TODO declare return type when all implementors have been refactored
     */
    public function Hydrate($object): array
    {
        $data = [
            'id' => $object->id,
            'guid' => $object->guid,
            'type' => $object->event_type,
            'occurred_at' => $object->occurred_at,
            'payload' => $object->payload
        ];
        return $data;
    }

    /**
     * Map (convert) from entity array to eloquent attributes array
     * @param $a_values
     * @return array
     */
    public function Map(array $a_values): array
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

    /**
     * Extract data from Event instance and return as raw data array
     * @param $event
     * @return array
     * @throws \Exception
     */
    public function Extract($event) {
        if (strpos(get_parent_class($event), 'GenericEvent')===false) {
            throw new \Exception('[EventHydrator][Extract] parameter must be instance of DomainEventInterface');
        }
        $data = [
            'guid' => $event->getGuid(),
            'event_type' => $event->getType(),
            'occurred_at' => $event->getOccurredAt(),
            'payload' => $event->getSerializedPayload()
        ];
        return $data;

    }
}