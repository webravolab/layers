<?php
namespace Webravo\Infrastructure\Repository;

use Webravo\Common\Entity\AbstractEntity;

/**
 * Interface StorableInterface
 * @package Webravo\Infrastructure\Repository
 *
 */
interface StorableInterface {

    public function persist($payload);

    public function persistEntity(AbstractEntity $entity);

    public function getByGuid($guid);

    public function getObjectByGuid($guid);

    public function update(AbstractEntity $entity);

    public function delete(AbstractEntity $entity);

    public function deleteByGuid($guid);

}
