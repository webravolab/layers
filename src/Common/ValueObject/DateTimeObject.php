<?php
namespace Webravo\Common\ValueObject;

use Carbon\Carbon;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;

class DateTimeObject extends AbstractValueObject
{
    /**
     * @var DateTimeObject
     */
    private $_dateObject;

    public function __construct($value = null)
    {
        if (is_string($value)) {
            // Try to convert any string date format to a date instance
            $value = self::convertString2Date($value);
        }
        if ($this->isSatisfiedBy($value)) {
            if (is_null($value)) {
                $this->_dateObject = null;
            }
            else {
                if ($value instanceof DateTime || $value instanceof Carbon) {
                    $this->_dateObject = DateTimeImmutable::createFromMutable($value);
                }
                elseif ($value instanceof DateTimeImmutable) {
                    $this->_dateObject = clone $value;
                }
            }
        }
        else {
            $class = get_class($this);
            throw(new ValueObjectException('Invalid value ' . $value . ' for object ' . $class));
        }
    }

    public static function fromString(string $value)
    {
        $date = self::convertString2Date($value);
        if ($date) {
            return new self($date);
        }
        throw(new ValueObjectException('Invalid date format ' . $value . ' for DateTimeObject'));
    }

    public static function fromDateTime(DateTime $value)
    {
        return new self($value);
    }

    /**
     * Return the current value
     * @return DateTimeInterface
     */
    public function getValue()
    {
        return $this->_dateObject;
    }


    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->_dateObject->format(DateTime::ISO8601);
    }

    public function toISOString()
    {
        return $this->_dateObject->format('Y-m-d H:i:s');
    }

    public function toRFC3339()
    {
        return $this->_dateObject->format(DateTime::RFC3339_EXTENDED);
    }

    public function isSatisfiedBy($value)
    {
        // Value could be Null, or instance of Carbon or Datetime
        if (is_null($value)) {
            return true;
        }
        if ($value instanceof Carbon || $value instanceof DateTimeInterface) {
            return true;
        }
        return false;
    }

    public function isEqual(AbstractValueObject $object)
    {
        if ($object instanceof DateTimeObject) {
            return $this->_dateObject == $object->_dateObject;
        }
        return false;
    }

    private static function convertString2Date($value): ?DateTimeInterface
    {
        // Try to convert any possible date(+time) format
        $date = \DateTime::createFromFormat(\DateTime::ISO8601, $value);
        if ($date === false) {
            $date = \DateTime::createFromFormat("Y-m-d\TH:i:s.uO", $value);
        }
        if ($date === false) {
            $date = \DateTime::createFromFormat("Y-m-d\TH:i:s", substr($value,0,19));
        }
        if ($date === false) {
            $date = \DateTime::createFromFormat("d/m/Y H:i:s", $value);
        }
        if ($date === false) {
            $date = \DateTime::createFromFormat('d/m/Y',$value);
        }
        if ($date === false) {
            $date = \DateTime::createFromFormat('Y-m-d',$value);
        }
        if ($date === false) {
            $date = \DateTime::createFromFormat('Y-m-d H:i:s.u',$value);
        }
        if ($date === false) {
            $date = \DateTime::createFromFormat('Y-m-d H:i:s',$value);
        }
        if ($date === false) {
            $date = \DateTime::createFromFormat('Y-m-d',substr($value,0,10));
        }
        if ($date !== false) {
            return $date;
        }
        return null;
    }

}
