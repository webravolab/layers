<?php
namespace Tests\Entity;

use DateTimeInterface;

Class TestEntity extends \Webravo\Common\Entity\AbstractEntity implements TestEntityInterface
{
    protected $name = null;
    protected $foreign_key = null;
    protected $created_at = null;

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getForeignKey()
    {
        return $this->foreign_key;
    }

    public function setForeignKey($value)
    {
        $this->foreign_key = (int) $value;
    }

    public function getCreatedAt(): DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(DateTimeInterface $value)
    {
        $this->created_at = $value;
    }

    /**
     * Custom implementations of toArray()
     * @return array
     */
    public function toArray(): array
    {
        return [
            'guid' => $this->guid,
            'name' => $this->name,
            'fk_id' => $this->foreign_key,
            'created_at' => $this->created_at,
        ];
    }

    /**
     * Custom implementations of fromArray()
     * @param array $a_values
     * @return mixed|void
     */
    public function fromArray(array $a_values)
    {
        $this->setGuid($a_values['guid']);
        $this->setName($a_values['name']);
        $this->setForeignKey($a_values['fk_id']);
        $this->setCreatedAt($a_values['created_at']);
    }
}
