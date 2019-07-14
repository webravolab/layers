<?php

namespace Webravo\Common\Contracts;

use Webravo\Common\Entity\AbstractEntity;
use Webravo\Common\Entity\EntityInterface;

interface RepositoryInterface {

    // REPOSITORY USE STORE TO ACCESS DATA
    // REPOSITORY RECEIVE/RETURN ENTITY FROM/TO SERVICE
    // REPOSITORY RECEIVE/SEND DATA ARRAY FROM/TO STORE
    // REPOSITORY USE ENTITY toArray() and fromArray() TO CONVERT ENTITY <-> ARRAY

    public function getByGuid(string $guid): ?EntityInterface;

    public function persist(EntityInterface $entity);

    public function update(EntityInterface $entity);

    public function delete(EntityInterface $entity);

    public function deleteByGuid(string $guid);
}