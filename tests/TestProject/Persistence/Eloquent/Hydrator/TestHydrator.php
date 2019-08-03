<?php
namespace tests\TestProject\Persistence\Hydrator;

use Webravo\Common\Contracts\HydratorInterface;
use DateTime;

class TestHydrator implements HydratorInterface
{
    public function getSchema(): array {
        return ['fields' =>
            [
                [
                    'name' => 'guid',
                    'type' => 'string',
                    'mode' => 'required',
                ],
                [
                    'name' => 'name',
                    'type' => 'string',
                    'mode' => 'nullable',
                ],
                [
                    'name' => 'fk_id',
                    'type' => 'integer',
                    'mode' => 'nullable',
                ],
                [
                    'name' => 'created_at',
                    'type' => 'datetime',
                ],
            ]
        ];
    }

    public function hydrateEloquent($eloquent_object): array
    {
        $data = [
            'id' => $eloquent_object->id,
            'guid' => $eloquent_object->guid,
            'name' => $eloquent_object->name,
            'created_at' => $eloquent_object->created_at,
            'fk_id' => $eloquent_object->fk_id,
        ];
        return $data;
    }

    public function hydrateDatastore($datastore_object):array
    {
        $data = [
            'guid' => $datastore_object['guid'],
            'name' => $datastore_object['name'],
            'created_at' => $datastore_object['created_at'],
            'fk_id' => $datastore_object['fk_id'],
        ];
        return $data;
    }

    /**
     * map entity properties to Eloquent model attributes
     * @param array $a_values
     * @return array
     */
    public function mapEloquent(array $a_values): array
    {
        $data = [
            'guid' => (isset($a_values['guid']) ? $a_values['guid'] : ''),
            'name' => (isset($a_values['name']) ? $a_values['name'] : null),
            'fk_id' => (isset($a_values['fk_id']) ? $a_values['fk_id'] : null),
            'created_at' => (isset($a_values['created_at']) ? new DateTime($a_values['created_at']) : null),
        ];
        if (isset($a_values['id']) && $a_values['id'] > 0) {
            // Set ID only if exists and not empty
            $data['id'] = $a_values['id'];
        }
        return $data;
    }

    /**
     * map entity properties to Datastore entity attributes
     * @param array $a_values
     * @return array
     */
    public function mapDatastore(array $a_values): array
    {
        $data = [
            'guid' => (isset($a_values['guid']) ? $a_values['guid'] : ''),
            'name' => (isset($a_values['name']) ? $a_values['name'] : null),
            'fk_id' => (isset($a_values['fk_id']) ? $a_values['fk_id'] : null),
            'created_at' => (isset($a_values['created_at']) ? new DateTime($a_values['created_at']) : null),
        ];
        return $data;
    }

}
