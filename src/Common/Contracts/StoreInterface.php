<?php
namespace Webravo\Common\Contracts;

use Webravo\Common\Entity\AbstractEntity;

/**
 * Interface StorableInterface
 * to be extended by store interface and implemented by store
 * @package Webravo\Infrastructure\Repository
 *
 */
interface StoreInterface {

    // STORE USE ELOQUENT OR DATASTORE TABLES TO ACCESS DATA
    // STORE RECEIVE/RETURN PROPERTIES ARRAY FROM/TO REPOSITORY
    // STORE GET/SET ELOQUENT/DATASTORE ATTRIBUTES
    // STORE USE HYDRATOR TO CONVERT PROPERTIES ARRAY TO ELOQUENT/DATASTORE ATTRIBUTES (single or array)
    // STORE COULD PERSIST ENTITIES BY function persistEntity()

    public function getByGuid(string $guid);

    public function getObjectByGuid(string $guid);

    public function persistEntity(AbstractEntity $entity);

    public function append(array $a_properties);

    public function update(array $a_properties);

    public function delete(array $a_properties);

    public function deleteByGuid(string $guid);

}
