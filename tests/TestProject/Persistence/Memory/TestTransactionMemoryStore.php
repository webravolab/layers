<?php
namespace tests\TestProject\Persistence\Memory;

use \InvalidArgumentException;
use tests\TestProject\Infrastructure\Repository\TestTransactionStoreInterface;
use Webravo\Common\Entity\AbstractEntity;

class TestTransactionMemoryStore implements TestTransactionStoreInterface
{

    private $storage = [];

    public function persistEntity(AbstractEntity $entity)
    {
        $a_data = $entity->toArray();
        $this->append($a_data);
    }

    public function append(array $a_properties)
    {
        $guid = $a_properties['aggregate_id'] ?? null;
        if (!$guid) {
            throw new InvalidArgumentException("[Store][append] transaction does not have a valid guid");
        }
        if (isset($this->storage[$guid])) {
            throw new InvalidArgumentException("[Store][append] transaction with guid $guid already exists");
        }
        $this->storage[$guid] = $a_properties;
    }

    public function update(array $a_properties)
    {
        $guid = $a_properties['aggregate_id'] ?? null;
        if (!$guid) {
            throw new InvalidArgumentException("[Store][update] transaction does not have a valid guid");
        }
        if (!isset($this->storage[$guid])) {
            throw new InvalidArgumentException("[Store][update] transaction with guid $guid does not exists");
        }
        $this->storage[$guid] = $a_properties;
    }

    public function delete(array $a_properties)
    {
        $guid = $a_properties['aggregate_id'] ?? null;
        if (!$guid) {
            throw new InvalidArgumentException("[Store][delete] transaction does not have a valid guid");
        }
        $this->deleteByGuid($guid);
    }

    public function deleteByGuid(string $guid)
    {
        if (!isset($this->storage[$guid])) {
            return;
        }
        unset($this->storage[$guid]);
    }

    public function getByGuid(string $guid)
    {
        if (!isset($this->storage[$guid])) {
            return null;
        }
        return ($this->storage[$guid]);
    }

    public function getObjectByGuid(string $guid)
    {
        // TODO
        return $this->getByGuid($guid);
    }

}
