<?php
namespace Webravo\Application\Event;

use Webravo\Application\Exception\EventException;
use Webravo\Common\ValueObject\DateTimeObject;
use Webravo\Infrastructure\Service\GuidServiceInterface;
use Webravo\Infrastructure\Library\DependencyBuilder;
use DateTime;
use DateTimeInterface;

abstract class GenericEvent implements EventInterface {

    /**
     * @var string
     */
    private $guid;

    /**
     * @var Webravo\Common\ValueObject\DateTimeObject;
     */
    private $occurred_at;

    /**
     * @var string
     */
    private $type;

    /**
     * The event payload
     * @var 
     */
    private $payload;
    
    
    public function __construct($type, ?DateTime $occurred_at = null) {

        $this->type = $type;
        $guidService = DependencyBuilder::resolve('Webravo\Infrastructure\Service\GuidServiceInterface');
        $this->guid = $guidService->generate()->getValue();
        if (!is_null($occurred_at)) {
            $this->setOccurredAt($occurred_at);
        }
        else {
            $this->setOccurredAt(new DateTime());
        }
    }

    public function getGuid():string {
        return $this->guid;
    }
    
    public function setGuid(string $guid) {
        $this->guid = $guid;
    }

    public function getOccurredAt(): ?DateTimeInterface {
        return $this->occurred_at->getValue();
    }

    public function setOccurredAt($occurred_at) {
        $this->occurred_at = new DateTimeObject($occurred_at);
    }

    public function setType(string $type) {
        $this->type = $type;
    }

    public function getType(): string {
        return $this->type;
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

    public function fromArray(array $data)
    {
        if (isset($data['guid'])) { $this->setGuid($data['guid']); }
        if (isset($data['type'])) { $this->setType($data['type']); }
        if (isset($data['occurred_at'])) { $this->setOccurredAt($data['occurred_at']); }
        if (isset($data['payload'])) {
            if (is_string($data['payload'])) {
                $payload = json_decode($data['payload'],true);
                if ($payload !== null) {
                    $this->setPayload($payload);
                } else {
                    $this->setPayload($data['payload']);
                }
            }
            else {
                $this->setPayload($data['payload']);
            }
        }
        else {
            $this->setPayload(null);
        }
    }        

    public static function buildFromArray(array $data): EventInterface
    {
        if (isset($data['type'])) {
            $eventName = $data['type'];
            if (strpos($eventName, '\\') === false && strpos($eventName, 'Project\\Domain\\Event\\') === false) {
                $eventName = 'Project\\Domain\\Event\\' . $eventName;
            }
            try {
                $class = new \ReflectionClass($eventName);
                $eventInstance = $class->newInstance();
                $eventInstance->fromArray($data);
                return $eventInstance;
            }
            catch (\ReflectionException $e) {
                throw new EventException('Event ' . $eventName . ' not found', 103);
            }
        }
        throw new EventException('[GenericEvent][buildFromArray] Event has not a valid type: ' . serialize($data), 104);
    }

    public function getSerializedPayload(): string
    {
        $json = json_encode($this->toArray());
        return $json;
    }

    public function setSerializedPayload(string $payload_serialized): string
    {
        if (is_string($payload_serialized)) {
            $payload = json_decode($payload_serialized,true);
            if ($payload !== null) {
                $this->setPayload($payload);
            } else {
                $this->setPayload($payload_serialized);
            }
        }
        else {
            $this->setPayload($payload_serialized);
        }
    }

    public function getSerializedEvent(): string
    {
        $json = json_encode($this->toArray());
        return $json;
    }

    public static function buildFromSerializedEvent(string $event_serialized): ?EventInterface
    {
        if (is_string($event_serialized)) {
            $event_array = json_decode($event_serialized, true);
            if ($event_array !== null) {
                return static::buildFromArray($event_array);
            }
        }
        return null;
    }
}