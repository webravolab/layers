<?php

namespace Webravo\Application\Command;

use Webravo\Application\Command\CommandInterface;
use Webravo\Application\Event\EventInterface;
use Webravo\Application\Exception\CommandException;
use Webravo\Infrastructure\Library\DependencyBuilder;
use Webravo\Common\Entity\AbstractEntity;
use Webravo\Common\ValueObject\DateTimeObject;
use DateTime;
use DateTimeInterface;
use ReflectionClass;

abstract class GenericCommand extends AbstractEntity implements CommandInterface
{
    /**
     * The command name
     * @var string
     */
    protected $command_name = null;

    /**
     * The binding key used in topic queue (optional)
     * @var string
     */
    protected $binding_key = null;

    /**
     * The queue name (optional)
     * @var string
     */
    protected $queue_name = null;

    /**
     * The header parameters (optional)
     * @var array
     */
    protected $header = array();

    /**
     * The command creation date+time
     * @var Webravo\Common\ValueObject\DateTimeObject;
     */
    private $created_at;

    /**
     * The command class name, used to rebuild the event from raw data
     * @var
     */
    private $class_name;

    public function __construct(?DateTime $created_at = null)
    {
        parent::__construct();
        $this->class_name = get_class($this);
        if (!is_null($created_at)) {
            $this->setCreatedAt($created_at);
        } else {
            $this->setCreatedAt(new DateTime());
        }
    }

    public function getCommandName(): string
    {
        return $this->command_name;
    }

    public function setCommandName($value)
    {
        $this->command_name = $value;
    }

    public function getBindingKey(): ?string
    {
        return $this->binding_key;
    }

    public function setBindingKey($value)
    {
        $this->binding_key = $value;
    }

    public function getQueueName(): ?string
    {
        return $this->queue_name;
    }

    public function setQueueName($value)
    {
        $this->queue_name = $value;
    }

    public function getHeader(): array
    {
        return $this->header;
    }

    public function setHeader(array $value)
    {
        $this->header = $value;
    }

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->created_at->getValue();
    }

    public function setCreatedAt($created_at)
    {
        $this->created_at = new DateTimeObject($created_at);
    }

    public function setClassName(string $name)
    {
        $this->class_name = $name;
    }

    public function getClassName(): string
    {
        return $this->class_name;
    }

    public function toArray(): array
    {
        $data = [
            'guid' => $this->getGuid(),
            'command' => $this->getCommandName($this),
            'binding_key' => $this->getBindingKey(),
            'queue_name' => $this->getQueueName(),
            'header' => $this->getHeader(),
            'created_at' => $this->created_at->toRFC3339(),
            'class_name' => $this->getClassName(),
        ];
        return $data;
    }

    public function fromArray(array $data)
    {
        if (isset($data['guid'])) {
            $this->setGuid($data['guid']);
        }
        if (isset($data['command'])) {
            $this->setCommandName($data['command']);
        }
        if (isset($data['binding_key'])) {
            $this->setBindingKey($data['binding_key']);
        }
        if (isset($data['queue_name'])) {
            $this->setQueueName($data['queue_name']);
        }
        if (isset($data['created_at'])) {
            $this->setCreatedAt($data['created_at']);
        }
        if (isset($data['header'])) {
            $this->setHeader($data['header']);
        }
        if (isset($data['class_name'])) {
            $this->setClassName($data['class_name']);
        }
    }

    public static function buildFromArray(array $data)
    {
        $commandInstance = null;
        if (isset($data['class_name'])) {
            $commandName = $data['class_name'];
            $commandInstance = DependencyBuilder::resolve($commandName);
            if (!$commandInstance) {
                try {
                    $class = new ReflectionClass($commandName);
                    $commandInstance = $class->newInstanceWithoutConstructor();
                } catch (\ReflectionException $e) {
                    // Class not found through reflection... continue
                    $commandInstance = null;
                }
            }
        }
        if (!$commandInstance && isset($data['command'])) {
            $commandName = $data['command'];
            $commandInstance = DependencyBuilder::resolve($commandName);
            if (!$commandInstance) {
                if (strpos($commandName, '\\') === false) {
                    // Not a fully qualified name ... try adding well-known namespaces
                    $commandName = 'Project\\Domain\\Command\\' . $commandName;
                    $commandInstance = DependencyBuilder::resolve($commandName);
                }
            }
        }
        if ($commandInstance) {
            // $commandInstance->setCommandName($commandName);
            $commandInstance->fromArray($data);
            return $commandInstance;
        }
        throw new EventException('[GenericCommand][buildFromArray] Command has not a valid class name nor command name: ' . serialize($data), 104);
        /*
        if (isset($data['command'])) {
            $commandName = $data['command'];
            if (strpos($commandName, '\\') === false && strpos($commandName, 'Project\\Domain\\Command\\') === false) {
                $commandName = 'Project\\Domain\\Command\\' . $commandName;
            }
            try {
                // Generate a new instance of the real command class based on its name
                $class = new \ReflectionClass($commandName);
                $commandInstance = $class->newInstanceWithoutConstructor();
                $commandInstance->setCommandName($commandName);
                // Delegate class to fill other command custom attributes
                $commandInstance->fromArray($data);
                // Return the new command instance
                return $commandInstance;
            }
            catch (\ReflectionException $e) {
                throw new CommandException('Command ' . $commandName . ' not found', 103);
            }
        }
        */
    }

    /*
    public function getSerializedPayload(): string
    {
        $json = json_encode($this->toArray());
        return $json;
    }
    */

    public function getSerializedCommand(): string
    {
        return json_encode($this->toArray());
    }

    public static function buildFromSerializedCommand(string $command_serialized): ?CommandInterface
    {
        if (is_string($command_serialized)) {
            $command_array = json_decode($command_serialized, true);
            if ($command_array !== null) {
                return static::buildFromArray($command_array);
            }
        }
        return null;
    }
}