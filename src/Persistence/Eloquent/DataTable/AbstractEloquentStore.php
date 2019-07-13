<?php

namespace Webravo\Persistence\Eloquent\DataTable;

use Webravo\Common\Entity\AbstractEntity;
use Webravo\Infrastructure\Library\DependencyBuilder;
use Webravo\Common\Contracts\StoreInterface;
use Webravo\Common\Contracts\HydratorInterface;

abstract class AbstractEloquentStore implements StoreInterface {

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

    abstract function append(array $data);

    abstract function getByGuid(STRING $guid);

    abstract function getObjectByGuid(string $guid);

    abstract function update(array $a_properties);

    abstract function delete(array $a_properties);

    abstract function deleteByGuid(string $guid);

}