<?php
namespace test\TestProject\Domain\Service;

interface TestServiceInterface
{

    /**
     * Store entity
     * @param EntityInterface $page
     * @return mixed
     */
    public function create(EntityInterface $page);

    /**
     * Update entity
     * @param EntityInterface $page
     * @return mixed
     */
    public function update(EntityInterface $page);

    /**
     * Delete entity
     * @param EntityInterface $page
     * @return mixed
     */
    public function delete(EntityInterface $page);


    /**
     * Delete entity by it's guid
     * @param string $guid
     * @return mixed
     */
    public function deleteByGuid(string $guid);

    /**
     * Retrieve entity by Guid
     * @param string $guid
     * @return mixed
     */
    public function getByGuid(string $guid);

}
