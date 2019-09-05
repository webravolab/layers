<?php

namespace Webravo\Common\Domain;

use Webravo\Common\Entity\EntityInterface;

interface AggregateRootInterface extends EntityInterface
{
    public function setAggregateId($aggregate_id);

    public function getAggregateId();
}