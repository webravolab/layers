<?php
namespace Webravo\Common\Entity;

use Webravo\Common\Entity\AbstractEntity;
use DateTime;

class DataStoreCommandEntity extends AbstractEntity
{

    private $command_name = null;
    private $binding_key = null;
    private $queue_name = null;
    private $header = array();
    private $payload;
    private $created_at;


    public function __construct()
    {
        $this->created_at = new DateTime;
    }

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

    public function setPayload($value)
    {
        $this->payload = $value;
    }

    public function getPayload()
    {
        return $this->payload;
    }

    public function setCreatedAt($value)
    {
        $this->created_at = $value;
    }

    public function getCreatedAt()
    {
        return $this->created_at;
    }

    public function toArray(): array
    {
        return [
            'guid' => $this->getGuid(),
            'command' => $this->getCommandName(),
            'binding_key' => $this->getBindingKey(),
            'queue_name' => $this->getQueueName(),
            'header' => $this->getHeader(),
            'payload' => $this->getPayload(),
            'created_at' => $this->getCreatedAt(),
        ];
    }

    public function fromArray(array $a_values)
    {
        if (isset($a_values['guid'])) { $this->setGuid($a_values['guid']); }
        if (isset($a_values['command'])) { $this->setCommandName($a_values['command']); }
        if (isset($a_values['binding_key'])) { $this->setBindingKey($a_values['binding_key']); }
        if (isset($a_values['queue_name'])) { $this->setQueueName($a_values['queue_name']); }
        if (isset($a_values['created_at'])) { $this->setCreatedAt($a_values['created_at']); }
        if (isset($a_values['payload'])) {
            if (is_string($a_values['payload'])) {
                $payload = json_decode($a_values['payload'], true);
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
        if (isset($a_values['header'])) {
            if (is_string($a_values['header'])) {
                $header = json_decode($a_values['header'], true);
                if ($payload !== null) {
                    $this->setHeader($header);
                } else {
                    $this->setHeader($a_values['header']);
                }
            }
            else {
                $this->setHeader($a_values['header']);
            }
        }
        else {
            $this->setHeader([]);
        }
    }

    /**
     * Custom replacement of toArray() to return serialized version of payload
     * @return array
     */
    public function toSerializedArray(): array {
        return [
            'guid' => $this->getGuid(),
            'command' => $this->getCommandName(),
            'binding_key' => $this->getBindingKey(),
            'queue_name' => $this->getQueueName(),
            'header' => json_encode($this->getHeader()),
            'payload' => json_encode($this->getPayload()),
        ];
    }
}