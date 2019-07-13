<?php
namespace Webravo\Common\Contracts;

/**
 * Interface StorableInterface
 * to be extended by store interface and implemented by store
 * @package Webravo\Infrastructure\Repository
 *
 */
interface StoreInterface {

    // STORE USE ELOQUENT MODEL TO ACCESS DATA
    // STORE RECEIVE/RETURN PROPERTIES ARRAY FROM/TO REPOSITORY
    // STORE GET/SET ELOQUENT ATTRIBUTES FROM/TO ELOQUENT MODEL
    // STORE USE HYDRATOR TO CONVERT PROPERTIES ARRAY TO ELOQUENT ATTRIBUTES (single or array)

    public function getByGuid(string $guid);

    public function getObjectByGuid(string $guid);

    public function append(array $a_properties);

    public function update(array $a_properties);

    public function delete(array $a_properties);

    public function deleteByGuid(string $guid);

}
