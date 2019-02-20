<?php

namespace Webravo\Application\Command;

use Webravo\Application\Command\CommandInterface;
use Webravo\Application\Exception\CommandException;

abstract class GenericCommand implements CommandInterface {

    protected $command_name = null;
    protected $binding_key = null;
    protected $queue_name = null;
    protected $header = array();

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

    public function toArray(): array
    {
        $data = [
            'command' => get_class($this),
            'binding_key' => $this->binding_key,
            'queue_name' => $this->queue_name,
            'header' => $this->getHeader()
        ];
        return $data;
    }

    public function fromArray(array $data)
    {
        // TO BE OVERRIDDEN BY INSTANCES
    }

    public static function buildFromArray(array $data): CommandInterface
    {
        if (isset($data['command'])) {
            $commandName = $data['command'];
            if (strpos($commandName, 'Project\\Domain\\Command\\') === false) {
                $commandClassName = 'Project\\Domain\\Command\\' . $commandName;
            }
            try {
                // Generate a new instance of the real command class based on its name
                $class = new \ReflectionClass($commandClassName);
                $commandInstance = $class->newInstanceWithoutConstructor();
                // Set common attributes
                $commandInstance->setCommandName($commandName);
                if (isset($data['binding_key'])) {
                    $commandInstance->setBindingKey($data['binding_key']);
                }
                if (isset($data['queue_name'])) {
                    $commandInstance->setQueueName($data['queue_name']);
                }
                if (isset($data['header'])) {
                    $commandInstance->setHeader($data['header']);
                }
                // Delegate class to fill other command custom attributes
                $commandInstance->fromArray($data);
                // Return the new command instance
                return $commandInstance;
            }
            catch (\ReflectionException $e) {
                throw new CommandException('Command ' . $commandName . ' not found', 103);
            }
        }
    }

    public function getSerializedPayload(): string
    {
        $json = json_encode($this->toArray());
        return $json;
    }

}