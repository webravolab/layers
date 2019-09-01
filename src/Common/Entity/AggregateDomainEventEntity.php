<?php
namespace Webravo\Common\Entity;

use Webravo\Common\Entity\AbstractEntity;
use Webravo\Common\ValueObject\DateTimeObject;
use DateTimeInterface;

class AggregateDomainEventEntity extends AbstractEntity
{
    private $aggregate_type;
    private $aggregate_id;
    private $event_type;
    private $version;
    private $occurred_at;
    private $payload;

    public function setAggregateType($value): void
    {
        $this->aggregate_type = $value;
    }

    public function getAggregateType()
    {
        return $this->aggregate_type;
    }

    public function setAggregateId($value): void
    {
        $this->aggregate_id = $value;
    }

    public function getAggregateId()
    {
        return $this->aggregate_id;
    }

    public function setEventType($value): void
    {
        $this->event_type = $value;
    }

    public function getEventType()
    {
        return $this->event_type;
    }

    public function setVersion(int $value): void
    {
        $this->version = $value;
    }

    public function getVersion()
    {
        return (int) $this->version;
    }

    public function setOccurredAt($value): void
    {
        $this->occurred_at = new DateTimeObject($value);
    }

    public function getOccurredAt(): ?DateTimeInterface
    {
        if ($this->occurred_at instanceof DateTimeObject) {
            return $this->occurred_at->getValue();
        }
        return null;
    }

    public function setPayload($value): void
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
            'aggregate_type' => $this->getAggregateType(),
            'aggregate_id' => $this->getAggregateId(),
            'event_type' => $this->getEventType(),
            'version' => $this->getVersion(),
            'occurred_at' => $this->getOccurredAt(),
            'payload' => $this->getPayload(),
        ];
    }

    public function fromArray(array $a_values): void
    {
        if (isset($a_values['guid'])) { $this->setGuid($a_values['guid']); }
        if (isset($a_values['aggregate_type'])) { $this->setAggregateType($a_values['aggregate_type']); }
        if (isset($a_values['aggregate_id'])) { $this->setAggregateId($a_values['aggregate_id']); }
        if (isset($a_values['type'])) { $this->setEventType($a_values['type']); }
        if (isset($a_values['version'])) { $this->setVersion($a_values['version']); }
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