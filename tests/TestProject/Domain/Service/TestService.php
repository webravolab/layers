<?php
namespace test\TestProject\Domain\Service;

use Webravo\Infrastructure\Library\DependencyBuilder;

class TestService implements TestServiceInterface
{

    // SERVICE MUST USE ONLY ENTITY OBJECTS
    // SERVICE USE REPOSITORY TO ACCESS DATA

    protected $repository;

    public function __construct(?test\TestProject\Domain\Repository\TestRepositoryInterface $repository)
    {
        if (is_null($repository)) {
            $this->repository = DependencyBuilder::resolve('test\TestProject\Domain\Repository\TestRepositoryInterface');
        }
        else {
            $this->repository = $repository;
        }
    }

    /**
     * Store entity
     * @param EntityInterface $page
     * @return mixed
     */
    public function create(EntityInterface $page)
    {

    }

    /**
     * Update entity
     * @param EntityInterface $page
     * @return mixed
     */
    public function update(EntityInterface $page)
    {

    }

    /**
     * Delete entity
     * @param EntityInterface $page
     * @return mixed
     */
    public function delete(EntityInterface $page)
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

    }

}
