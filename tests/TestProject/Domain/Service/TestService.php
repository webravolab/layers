<?php
namespace tests\TestProject\Domain\Service;

use tests\TestProject\Domain\Entity\TestEntity;
use Webravo\Infrastructure\Library\DependencyBuilder;
use Webravo\Common\Entity\EntityInterface;
use tests\TestProject\Domain\Repository\TestRepositoryInterface;


class TestService implements TestServiceInterface
{

    // SERVICE MUST USE ONLY ENTITY OBJECTS
    // SERVICE USE REPOSITORY TO ACCESS DATA

    protected $repository;

    public function __construct(?TestRepositoryInterface $repository)
    {
        if (is_null($repository)) {
            $this->repository = DependencyBuilder::resolve('tests\TestProject\Domain\Repository\TestRepositoryInterface');
        }
        else {
            $this->repository = $repository;
        }
    }

    /**
     * Store entity
     * @param EntityInterface
     * @return mixed
     */
    public function create(EntityInterface $entity)
    {
        $this->repository->persist($entity);
    }

    /**
     * Update entity
     * @param EntityInterface
     * @return mixed
     */
    public function update(EntityInterface $entity)
    {

    }

    /**
     * Delete entity
     * @param EntityInterface
     * @return mixed
     */
    public function delete(EntityInterface $entity)
    {

    }


    /**
     * Delete entity by it's guid
     * @param string $guid
     * @return mixed
     */
    public function deleteByGuid(string $guid)
    {

    }

    /**
     * Retrieve entity by Guid
     * @param string $guid
     * @return mixed
     */
    public function getByGuid(string $guid)
    {
        $a_properties = $this->repository->getByGuid($guid);
        $entity = TestEntity::buildFromArray($a_properties);
        return $entity;
    }

}
