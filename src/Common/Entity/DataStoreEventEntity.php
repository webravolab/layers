<?php
namespace Webravo\Common\Entity;

use \Webravo\Common\Entity\AbstractEntity;
use Webravo\Common\ValueObject\DateTimeObject;
use DateTimeInterface;

class DataStoreEventEntity extends AbstractEntity
{

    private $type;
    private $occurred_at;
    private $payload;

    public function setType($value)
    {
        $this->type = $value;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setOccurredAt($value)
    {
        $this->occurred_at = new DateTimeObject($value);
    }

    public function getOccurredAt():\DateTimeInterface
    {
        if ($this->occurred_at instanceof DateTimeObject) {
            return $this->occurred_at->getValue();
        }
    }

    public function setPayload($value)
    {
        $this->payload = $value;
    }

    public function getPayload()
    {
        return $this->payload;
    }

    public function toArray(): array
    {
        return [
            'guid' => $this->getGuid(),
            'type' => $this->getType(),
            'occurred_at' => $this->getOccurredAt(),
            'payload' => $this->getPayload(),
        ];
    }

    public function fromArray(array $a_values)
    {
        if (isset($a_values['guid'])) { $this->setGuid($a_values['guid']); }
        if (isset($a_values['type'])) { $this->setType($a_values['type']); }
        if (isset($a_values['occurred_at'])) { $this->setOccurredAt($a_values['occurred_at']); }
        if (isset($a_values['payload'])) {
            if (is_string($a_values['payload'])) {
                $payload = json_decode($a_values['payload']);
                if ($payload !== null) {
                    $this->setPayload($payload);
                } else {
                    $this->setPayload($a_values['payload']);
                }
            }
            else {
                $this->setPayload($a_values['payload']);
            }
        }
        else {
            $this->setPayload(null);
        }
    }

    /**
     * Custom replacement of toArray() to return serialized version of payload
     * @return array
     */
    public function toSerializedArray(): array {
        return [
            'guid' => $this->getGuid(),
            'type' => $this->getType(),
            'occurred_at' => $this->getOccurredAt(),
            'payload' => $this->getSerializedPayload(),
        ];
    }

    /**
     * Custom function to return a Json serialized version of Payload
     * @return string
     */
    public function getSerializedPayload(): string
    {
        return json_encode($this->getPayload());
    }

}