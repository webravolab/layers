<?php
namespace Webravo\Persistence\Eloquent\Hydrators;

use Webravo\Infrastructure\Repository\HydratorInterface;
use Webravo\Persistence\Eloquent\DataTable\JobDataTable;

class JobHydrator implements HydratorInterface {

    public function Hydrate($object) {

        $data = [
            'id' => $object->id,
            'guid' => $object->guid,
            'name' => $object->name,
            'channel' => $object->channel,
            'status' => $object->status,
            'created_at' => $object->created_at,
            'delivered_at' => $object->created_at,
            'payload' => $object->payload,
            'header' => $object->header,
        ];
        return JobDataTable::buildFromArray($data);
    }

    public function Map(array $a_values): array
    {
        // TODO: Implement Map() method.
        throw new \Exception('Unimplemented');
    }

    public function Extract($job) {
        $data = [
            'guid' => $job->getGuid(),
            'name' => $job->getName(),
            'channel' => $job->getChannel(),
            'status' => $job->getStatus(),
            'created_at' => $job->getCreatedAt(),
            'delivered_at' => $job->getDeliveredAt(),
            'payload' => $job->getPayload(),
            'header' => $job->getRawHeader()
        ];
        return $data;
    }
}