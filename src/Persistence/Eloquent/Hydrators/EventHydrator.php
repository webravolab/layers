<?php
namespace Webravo\Persistence\Eloquent\Hydrators;

use Webravo\Infrastructure\Repository\HydratorInterface;

class EventHydrator implements HydratorInterface {


    public function Hydrate($eloquent_object): array
    {
        // TODO: Implement Hydrate() method.
        throw new \Exception('Unimplemented');
    }

    public function Map(array $a_values): array
    {
        // TODO: Implement Map() method.
        throw new \Exception('Unimplemented');
    }

    public function Extract($event) {
        if (strpos(get_parent_class($event), 'GenericEvent')===false) {
            throw new \Exception('EventDataTable: parameter must be instance of DomainEventInterface');
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