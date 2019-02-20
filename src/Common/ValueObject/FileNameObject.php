<?php
namespace Webravo\Common\ValueObject;

class FileNameObject extends AbstractValueObject
{
    public function isSatisfiedBy($value)
    {
        // TODO - the simplest validation
        if (!empty($value)) {
            return true;
        }
        return false;
    }


}
