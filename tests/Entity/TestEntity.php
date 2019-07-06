<?php
namespace Tests\Entity;

Class TestEntity extends \Webravo\Common\Entity\AbstractEntity
{
    protected $name = null;
    protected $foreign_key = null;

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setForeignKey($value)
    {
        $this->foreign_key = (int) $value;
    }

    public function getForeignKey()
    {
        return $this->foreign_key;
    }

    public function toArray(): array
    {
        return [
            'guid' => $this->guid,
            'name' => $this->name,
            'fk_id' => $this->foreign_key,
        ];
    }

    public function fromArray(array $a_values)
    {
        $this->guid = $a_values['guid'];
        $this->name = $a_values['name'];
        $this->foreign_key = $a_values['fk_id'];
    }
}
