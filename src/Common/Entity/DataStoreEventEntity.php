<?php
namespace Webravo\Common\Entity;

use \Webravo\Common\Entity\AbstractEntity;

class DataStoreEventEntity extends AbstractEntity
{

    private $type;
    private $occurred_at;
    private $payload;
    
    public function toArray(): array
    {
        return [
            'guid' => $this->getGuid(),
            'type' => $this->type,
            'occured_at' => $this->occurred_at,
            'payload' => $this->payload,
        ];
    }

    public function fromArray(array $a_values)
    {
        if (isset($a_values['guid'])) { $this->guid = $a_values['guid']; }
        if (isset($a_values['type'])) { $this->type = $a_values['type']; }
        if (isset($a_values['occurred_at'])) { $this->occurred_at = $a_values['occurred_at']; }
        if (isset($a_values['payload'])) { $this->payload = $a_values['payload'];}
    }

}