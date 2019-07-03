<?php
namespace Webravo\Application\Event;

use Webravo\Application\Exception\EventException;
use Webravo\Infrastructure\Service\GuidServiceInterface;
use Webravo\Infrastructure\Library\DependencyBuilder;
use DateTime;

abstract class GenericEvent implements EventInterface {

    private $guidService;
    private $guid;
    private $occurred_at;
    private $type;

    public function __construct($type, ?DateTime $occurred_at = null) {

        $this->guidService = DependencyBuilder::resolve('Webravo\Infrastructure\Service\GuidServiceInterface');

        $this->guid = $this->guidService->generate()->getValue();
        if (!is_null($occurred_at)) {
            $this->occurred_at = $occurred_at;
        }
        else {
            $this->occurred_at = new DateTime();
        }
        $this->type = $type;
    }

    public function setGuid($guid) {
        $this->guid = $guid;
    }

    public function getGuid() {
        return $this->guid;
    }

    public function getOccurredAt(): ?DateTime {
        return $this->occurred_at;
    }

    public function setOccurredAt(DateTime $occurred_at) {
        $this->occurred_at = $occurred_at;
    }

    public function setType($type) {
        $this->type = $type;
    }

    public function getType(): string {
        return $this->type;
    }

    abstract public function setPayload($value);

    abstract public function getPayload();

    abstract public function toArray(): array;

    public static function buildFromArray(array $data): EventInterface
    {
        if (isset($data['type'])) {
            $eventName = $data['type'];
            if (strpos($eventName, 'Project\\Domain\\Event\\') === false) {
                $eventClassName = 'Project\\Domain\\Event\\' . $eventName;
            }
            try {
                $class = new \ReflectionClass($eventClassName);
                $eventInstance = $class->newInstance();
                $eventInstance->fromArray($data);
                return $eventInstance;
            }
            catch (\ReflectionException $e) {
                throw new EventException('Event ' . $eventName . ' not found', 103);
            }
        }
    }

    public function getSerializedPayload(): string
    {
        $json = json_encode($this->toArray());
        return $json;
    }

}