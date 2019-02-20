<?php

namespace Webravo\Persistence\Repository;

use Webravo\Infrastructure\Repository\HydratorInterface;
use Webravo\Infrastructure\Library\DependencyBuilder;

abstract class AbstractDataTable {

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

    abstract function getByGuid($guid);

    abstract function persist($object);

}