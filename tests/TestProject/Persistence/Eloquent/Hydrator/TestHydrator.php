<?php
namespace tests\TestProject\Persistence\Hydrator;

use Webravo\Infrastructure\Repository\HydratorInterface;
use DateTime;

class TestHydrator implements HydratorInterface
{
    public function hydrate($eloquent_object)
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

    /**
     * map entity properties to eloquent model attributes
     * @param array $a_values
     * @return array
     */
    public function map(array $a_values): array
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
}
