<?php

namespace Tests\Entity;

use DateTimeInterface;

interface TestEntityInterface extends \Webravo\Common\Entity\EntityInterface
{
    // Additional getters/setters for custom entity properties

    public function getName();

    public function setName($name);

    public function getForeignKey();

    public function setForeignKey($value);

    public function getCreatedAt(): DateTimeInterface;

    public function setCreatedAt(DateTimeInterface $value);

}
