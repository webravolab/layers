<?php
namespace Webravo\Common\Entity;

use Webravo\Common\Entity\AbstractEntity;
use Webravo\Common\ValueObject\DateTimeObject;
use DateTimeInterface;

class EventEntity extends AbstractEntity
{

    private $type;
    private $class_name;
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

    public function setClassName($class_name)
    {
        $this->class_name = $class_name;
    }

    public function getClassName()
    {
        return $this->class_name;
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
            'class_name' => $this->getClassName(),
            'occurred_at' => $this->getOccurredAt(),
            'payload' => $this->getPayload(),
        ];
    }

    public function fromArray(array $a_values)
    {
        if (isset($a_values['guid'])) { $this->setGuid($a_values['guid']); }
        if (isset($a_values['type'])) { $this->setType($a_values['type']); }
        if (isset($a_values['class_name'])) { $this->setClassName($a_values['class_name']); }
        if (isset($a_values['occurred_at'])) { $this->setOccurredAt($a_values['occurred_at']); }
        if (isset($a_values['payload'])) {
            if (is_string($a_values['payload'])) {
                $payload = json_decode($a_values['payload'],true);
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
     * Custom function to return a Json serialized version of Payload
     * @return string
     */
    public function getSerializedPayload(): string
    {
        return json_encode($this->getPayload());
    }

}