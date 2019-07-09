<?php
namespace Webravo\Persistence\Eloquent\Hydrators;

use Webravo\Infrastructure\Repository\HydratorInterface;
use Webravo\Persistence\Eloquent\DataTable\JobDataTable;

class JobHydrator implements HydratorInterface {

    /**
     * Convert eloquent attributes to array
     * @param $eloquent_object
     * @return array                    // TODO declare return type when all implementors have been refactored
     */
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
        return $data;
        // return JobDataTable::buildFromArray($data);
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
            'name' => (isset($a_values['name']) ? $a_values['name'] : 'undefined'),
            'channel' => (isset($a_values['channel']) ? $a_values['channel'] : 'undefined'),
            'status' => (isset($a_values['status']) ? $a_values['status'] : 'QUEUED'),
            'created_at' => (isset($a_values['created_at']) ? $a_values['created_at'] : null),
            'delivered_at' => (isset($a_values['delivered_at']) ? $a_values['delivered_at'] : null),
            'header' => json_encode($a_values['header']),
            'payload' => json_encode($a_values['payload']),
            // 'payload' => (isset($a_values['payload']) ? $a_values['payload'] : null),
            // 'header' => (isset($a_values['header']) ? $a_values['header'] : null),
        ];
        if (isset($a_values['id']) && $a_values['id'] > 0) {
            // Set ID only if exists and not empty
            $data['id'] = $a_values['id'];
        }
        return $data;
    }

    /**
     * Extract data from Job instance and return as raw data array
     * @param $event
     * @return array
     * @throws \Exception
     */
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