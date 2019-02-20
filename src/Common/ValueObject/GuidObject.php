<?php
namespace Webravo\Common\ValueObject;

class GuidObject extends AbstractValueObject
{
    public function isSatisfiedBy($value)
    {
        // Accept only 36 chars lenght string
        if (!empty($value)) {
            if (strlen($value) == 36) {
                return true;
            }
        }
        return false;
    }

    /**
     * Override getValue to return always string value of Guid
     * @return value|string
     */
    public function getValue() {
        return $this->__toString();
    }
}
