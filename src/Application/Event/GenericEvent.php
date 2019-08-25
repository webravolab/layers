<?php
namespace Webravo\Application\Event;

use tests\TestProject\Domain\Events\TestEvent;
use Webravo\Application\Exception\EventException;
use Webravo\Common\ValueObject\DateTimeObject;
use Webravo\Infrastructure\Service\GuidServiceInterface;
use Webravo\Infrastructure\Library\DependencyBuilder;
use DateTime;
use DateTimeInterface;
use ReflectionClass;

abstract class GenericEvent implements EventInterface
{
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
     * The event class name, used to rebuild the event from raw data
     * @var
     */
    private $class_name;

    /**
     * The event payload
     * @var
     */
    private $payload;


    public function __construct($type, ?DateTime $occurred_at = null)
    {
        $this->type = $type;
        $this->class_name = get_class($this);
        $guidService = DependencyBuilder::resolve('Webravo\Infrastructure\Service\GuidServiceInterface');
        $this->guid = $guidService->generate()->getValue();
        if (!is_null($occurred_at)) {
            $this->setOccurredAt($occurred_at);
        } else {
            $this->setOccurredAt(new DateTime());
        }
    }

    public function getGuid(): string
    {
        return $this->guid;
    }

    public function setGuid(string $guid)
    {
        $this->guid = $guid;
    }

    public function getOccurredAt(): ?DateTimeInterface
    {
        return $this->occurred_at->getValue();
    }

    public function setOccurredAt($occurred_at)
    {
        $this->occurred_at = new DateTimeObject($occurred_at);
    }

    public function setType(string $type)
    {
        $this->type = $type;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setClassName(string $name)
    {
        $this->class_name = $name;
    }

    public function getClassName(): string
    {
        return $this->class_name;
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
            'occurred_at' => $this->occurred_at->toRFC3339(),
            'payload' => $this->getPayload(),
        ];
    }

    public function fromArray(array $data)
    {
        if (isset($data['guid'])) {
            $this->setGuid($data['guid']);
        }
        if (isset($data['type'])) {
            $this->setType($data['type']);
        }
        if (isset($data['class_name'])) {
            $this->setClassName($data['class_name']);
        }
        if (isset($data['occurred_at'])) {
            $this->setOccurredAt($data['occurred_at']);
        }
        if (isset($data['payload'])) {
            if (is_string($data['payload'])) {
                $payload = json_decode($data['payload'], true);
                if ($payload !== null) {
                    $this->setPayload($payload);
                } else {
                    $this->setPayload($data['payload']);
                }
            } else {
                $this->setPayload($data['payload']);
            }
        } else {
            $this->setPayload(null);
        }
    }

    public static function buildFromArray(array $data): EventInterface
    {
        $eventInstance = null;
        if (isset($data['class_name'])) {
            $eventName = $data['class_name'];
            $eventInstance = DependencyBuilder::resolve($eventName);
            if (!$eventInstance) {
                try {
                    $class = new ReflectionClass($eventName);
                    $eventInstance = $class->newInstanceWithoutConstructor();
                } catch (\ReflectionException $e) {
                    // Class not found through reflection... continue
                    $eventInstance = null;
                }
            }
        }
        if (!$eventInstance && isset($data['type'])) {
            $eventName = $data['type'];
            $eventInstance = DependencyBuilder::resolve($eventName);
            if (!$eventInstance) {
                if (strpos($eventName, '\\') === false) {
                    // Not a fully qualified name ... try adding well-known namespaces
                    $eventName = 'Project\\Domain\\Event\\' . $eventName;
                    $eventInstance = DependencyBuilder::resolve($eventName);
                }
            }
        }
        if ($eventInstance) {
            // $eventInstance = $class->newInstance();
            $eventInstance->fromArray($data);
            return $eventInstance;
        }
        throw new EventException('[GenericEvent][buildFromArray] Event has not a valid class name nor type: ' . serialize($data), 104);
    }

    public function getSerializedPayload(): string
    {
        return json_encode($this->getPayload());
    }

    public function setSerializedPayload(string $payload_serialized): string
    {
        if (is_string($payload_serialized)) {
            $payload = json_decode($payload_serialized, true);
            if ($payload !== null) {
                $this->setPayload($payload);
            } else {
                $this->setPayload($payload_serialized);
            }
        } else {
            $this->setPayload($payload_serialized);
        }
    }

    public function getSerializedEvent(): string
    {
        return json_encode($this->toArray());
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