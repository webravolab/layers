<?php
namespace Webravo\Common\Entity;

use Webravo\Common\Entity\AbstractEntity;
use Webravo\Common\ValueObject\DateTimeObject;
use DateTimeInterface;
use ReflectionClass;
Use ReflectionProperty;

class DataStoreEventEntity extends AbstractEntity
{

    private $type;
    private $occurred_at;
    private $payload;

    /**
     * Array of all event properties, including properties created dynamically
     * @var array
     */
    protected $a_properties = ['guid','type','occurred_at','guid'];

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
        $class = new ReflectionClass($this);
        $methods = $class->getMethods();
        $properties = $class->getProperties();

        if (isset($a_values['guid'])) { $this->setGuid($a_values['guid']); }
        if (isset($a_values['type'])) { $this->setType($a_values['type']); }
        if (isset($a_values['occurred_at'])) { $this->setOccurredAt($a_values['occurred_at']); }

        foreach($a_values as $key => $value) {
            $key = strtolower($key);
            if (!in_array($key, $this->a_properties)) {
                $this->a_properties[] = $key;
                $this->{$key} = $value;
            }
        }

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
     * Custom replacement of toArray() to return serialized version of payload
     * @return array
     */
    public function toSerializedArray(): array {
        $a_return = [
            'guid' => $this->getGuid(),
            'type' => $this->getType(),
            'occurred_at' => $this->getOccurredAt(),
            'payload' => $this->getSerializedPayload(),
        ];
        foreach($this->a_properties as $property) {
            if (!array_key_exists($property, $a_return)) {
                $a_return[$property] = $this->{$property};
            }
        }
        /*
        $class = new ReflectionClass($this);
        $methods = $class->getMethods();
        $properties = $class->getProperties(ReflectionProperty::IS_PRIVATE);
        foreach ($properties as $property) {
            $key = $property->getName();
            $value = $property->getValue();
            $a_return[$key] = $value;
        }
        */
        return $a_return;
    }

    /**
     * Custom function to return a Json serialized version of Payload
     * @return string
     */
    public function getSerializedPayload(): string
    {
        return json_encode($this->getPayload());
    }

    // === Magic Functions to get / set additional parameters ===
    public function __get( $varName )
    {
        $method = $this->findGetterSetter('get', $varName);
        if ($method) {
            return $this->$method();
        }
        return null;
    }

    /*
    public function __set( $varName, $value )
    {
        $method = $this->findGetterSetter('set', $varName);
        if ($method) {
            $this->$method($value);
            return true;
        }
        return false;
    }

    private function findGetterSetter(string $getOrSet, string $varName): ?string
    {
        $class = new ReflectionClass($this);
        $methods = $class->getMethods();
        $separator = '';
        for ($attempt = 0; $attempt <= 5; $attempt++) {
            switch ($attempt) {
                case 0:
                case 3:
                    $variant = $varName;
                    break;
                case 1:
                case 4:
                    $variant = ucwords($varName, "-_");
                    break;
                case 2:
                case 5:
                    $variant = strtolower(str_replace('_', '', $varName));
                    break;
            }
            if ($attempt > 2) {
                $separator = '_';
            }
            $methodAttempt = $getOrSet . $separator . $variant;
            if (method_exists($this, $methodAttempt)) {
                return $methodAttempt;
            }
        }
        return null;
    }

    private function underscore2camel(string $value): string
    {
        return ucwords($value, "-_");
    }
    */
}