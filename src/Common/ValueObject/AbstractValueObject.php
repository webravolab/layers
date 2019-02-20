<?php
namespace Webravo\Common\ValueObject;

use Webravo\Common\Exception\ValueObjectException;

abstract class AbstractValueObject
{
    /**
     * @var string/object
     */
    private $value;

    /**
     * Return the object as a string
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->value;
    }

    /**
     * Create a new ValueObject
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
     * Return the current value
     * @return value
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set the new value
     * @param $value
     */
    public function setValue($value)
    {
        $this->value = $value;
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
     * @return mixed
     */
    abstract function isSatisfiedBy($value);
}
