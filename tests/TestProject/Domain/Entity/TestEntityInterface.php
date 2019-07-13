<?php

namespace tests\TestProject\Domain\Entity;

use Webravo\Common\ValueObject\DateTimeObject;

interface TestEntityInterface extends \Webravo\Common\Entity\EntityInterface
{
    // Additional getters/setters for custom entity properties

    public function getName();

    public function setName($name);

    public function getForeignKey();

    public function setForeignKey($value);

    public function getCreatedAt(): DateTimeObject;

    public function setCreatedAt(DateTimeObject $value);

}
