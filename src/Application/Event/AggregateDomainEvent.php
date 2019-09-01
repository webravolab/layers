<?php
namespace Webravo\Application\Event;

use DateTime;
use ReflectionClass;
use Webravo\Application\Exception\EventException;
use Webravo\Infrastructure\Library\DependencyBuilder;

abstract class AggregateDomainEvent extends GenericEvent
{
    private $aggregate_type;

    private $aggregate_id;

    private $version;

    public function __construct($type, $aggregate_type, $aggregate_id, ?DateTime $occurred_at = null)
    {
        parent::__construct($type, $occurred_at);
        $this->aggregate_type = $aggregate_type;
        $this->aggregate_id = $aggregate_id;
    }

    public function withVersion($version)
    {
        $this->version = $version;
        return $this;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function getAggregateType()
    {
        return $this->aggregate_type;
    }

    public function getAggregateId()
    {
        return $this->aggregate_id;
    }

    public function toArray(): array
    {
        $data = parent::toArray() + [
            'aggregate_type' => $this->getAggregateType(),
            'aggregate_id' => $this->getAggregateId(),
            'version' => (int) $this->getVersion(),
        ];
        return $data;
    }

    public function fromArray(array $data)
    {
        // Get base properties
        parent::fromArray($data);
        if (isset($data['aggregate_type'])) {
            $this->aggregate_type = $data['aggregate_type'];
        }
        if (isset($data['aggregate_id'])) {
            $this->aggregate_id = $data['aggregate_id'];
        }
        if (isset($data['version'])) {
            $this->version = (int) $data['version'];
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
        throw new EventException('[AggregateDomainEvent][buildFromArray] Event has not a valid class name nor type: ' . serialize($data), 104);
    }

}