<?php

namespace Webravo\Persistence\Repository;

use Webravo\Common\Entity\AbstractEntity;
use Webravo\Infrastructure\Repository\HydratorInterface;
use Webravo\Infrastructure\Library\DependencyBuilder;
use Webravo\Infrastructure\Repository\StorableInterface;

abstract class AbstractDataTable implements StorableInterface {

    protected $guid;

    protected $hydrator;

    public function __construct(HydratorInterface $hydrator) {
        $this->hydrator = $hydrator;
        $guidService = DependencyBuilder::resolve('Webravo\Infrastructure\Service\GuidServiceInterface');
        $this->guid = $guidService->generate()->getValue();
    }

    public function getGuid()
    {
        return $this->guid;
    }

    abstract function persist(AbstractEntity $object);

    abstract function getByGuid($guid);

    abstract function getObjectByGuid($guid);

    abstract function update(AbstractEntity $entity);

    abstract function delete(AbstractEntity $entity);

    abstract function deleteByGuid($guid);

}