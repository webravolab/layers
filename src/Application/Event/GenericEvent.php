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
     * @var GuidServiceInterface
     */
    private $guidService;

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

    public function __construct($type, ?DateTime $occurred_at = null) {

        $this->type = $type;
        $this->guidService = DependencyBuilder::resolve('Webravo\Infrastructure\Service\GuidServiceInterface');
        $this->guid = $this->guidService->generate()->getValue();
        if (!is_null($occurred_at)) {
            $this->setOccurredAt($occurred_at);
        }
        else {
            $this->setOccurredAt(new DateTime());
        }
    }

    public function setGuid($guid) {
        $this->guid = $guid;
    }

    public function getGuid() {
        return $this->guid;
    }

    public function getOccurredAt(): ?DateTimeInterface {
        return $this->occurred_at->getValue();
    }

    public function setOccurredAt($occurred_at) {
        $this->occurred_at = new DateTimeObject($occurred_at);
    }

    public function setType($type) {
        $this->type = $type;
    }

    public function getType(): string {
        return $this->type;
    }

    public function getClassName(): string {
        return get_class($this);
    }

    abstract public function setPayload($value);

    abstract public function getPayload();

    /**
     * Convert to array the base properties
     * (to be called as parent::toArray() from classes extending GenericEvent)
     * @return array
     */
    public function toArray(): array {
        return [
            'guid' => $this->getGuid(),
            'type' => $this->getType(),
            'class_name' => $this->getClassName(),
            'occurred_at' => $this->getOccurredAt(),
        ];
    }

    /**
     * Import base properties from an array
     * (to be called as parent::fromArray($data) from classes extending GenericEvent)
     * @param array $data
     */
    public function fromArray(array $data)
    {
        if (isset($data['guid'])) {
            $this->setGuid($data['guid']);
        }
        if (isset($data['type'])) {
            $this->setType($data['type']);
        }
        if (isset($data['occurred_at'])) {
            $this->setOccurredAt($data['occurred_at']);
        }
    }

    public static function buildFromArray(array $data): EventInterface
    {
        $eventName = null;
        if (isset($data['class_name']) && class_exists($data['class_name'])) {
            $eventName = $data['class_name'];
        }
        elseif (isset($data['type'])) {
            $eventName = $data['type'];
            if (strpos($eventName, '\\') === false && strpos($eventName, 'Project\\Domain\\Event\\') === false) {
                $eventName = 'Project\\Domain\\Event\\' . $eventName;
            }
        }
        if (!empty($eventName)) {
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

}