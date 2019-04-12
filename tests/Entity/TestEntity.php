<?php
namespace Tests\Entity;

Class TestEntity extends \Webravo\Common\Entity\AbstractEntity
{
    protected $name = null;

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function toArray(): array
    {
        return [
            'guid' => $this->guid,
            'name' => $this->name,
        ];
    }

    public function fromArray(array $a_values)
    {
        $this->guid = $a_values['guid'];
        $this->name = $a_values['name'];
    }
}
