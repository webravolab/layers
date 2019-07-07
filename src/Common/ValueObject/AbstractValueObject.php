<?php
namespace Webravo\Common\ValueObject;

use Webravo\Common\Exception\ValueObjectException;

abstract class AbstractValueObject
{
    /**
     * Create a new immutable ValueObject
     *
     * @param $value
     * @return void
     */
    public function __construct($value)
    {
        if ($this->isSatisfiedBy($value)) {
            $this->value = $value;
        }
        else {
            $class = get_class($this);
            throw(new ValueObjectException('Invalid value ' . $value . ' for object ' . $class));
        }
    }

    /**
     * @var string/object
     */
    protected $value;

    /**
     * Return the object as a string
     *
     * @return string
     */
    public function __toString(): string
    {
        return (string) $this->value;
    }


    /**
     * Return the current value
     * @return value
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Check if object value is equal to another object
     * @param AbstractValueObject $object
     * @return bool
     */
    public function isEqual(AbstractValueObject $object)
    {
        return ($this->value === $object->getValue());
    }

    /**
     * Validate value
     * @param $value
     * @return boolean
     */
    abstract function isSatisfiedBy($value);
}
