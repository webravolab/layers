<?php
namespace Webravo\Common\Entity;

use Webravo\Common\Entity\AbstractEntity;
use Webravo\Common\ValueObject\DateTimeObject;
use DateTimeInterface;

class DataStoreCommandEntity extends AbstractEntity
{

    /*
    private $type;
    private $occurred_at;
    private $payload;
    */

    private $command_name = null;
    private $binding_key = null;
    private $queue_name = null;
    private $header = array();


    public function setCommandName($value)
    {
        $this->command_name = $value;
    }

    public function getCommandName()
    {
        return $this->command_name;
    }

    public function setBindingKey($value)
    {
        $this->binding_key = $value;
    }

    public function getBindingKey()
    {
        return $this->binding_key;
    }

    public function setQueueName($value)
    {
        $this->queue_name = $value;
    }

    public function getQueueName()
    {
        return $this->queue_name;
    }

    public function setHeader($value)
    {
        $this->header = $value;
    }

    public function getHeader()
    {
        return $this->header;
    }

    public function toArray(): array
    {
        return [
            'command_name' => $this->getCommandName(),
            'binding_key' => $this->getBindingKey(),
            'queue_name' => $this->getQueueName(),
            'header' => $this->getHeader(),
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