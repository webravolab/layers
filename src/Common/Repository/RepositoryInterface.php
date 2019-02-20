<?php

namespace Webravo\Common\Repository;

use Webravo\Common\Entity\EntityInterface;

interface RepositoryInterface {

    public function getByGuid($guid);

    public function persist(EntityInterface $object);
}