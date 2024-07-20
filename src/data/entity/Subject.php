<?php

namespace dev\suvera\exms\data\entity;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;

#[Entity]
#[Table(name: "subject")]
class Subject implements \JsonSerializable {
    #[Id]
    #[GeneratedValue]
    #[Column(name: "id", type: "integer", nullable: false)]
    public int $id;

    #[Column(name: "name", type: "string", nullable: false, length: 255, unique: true)]
    public string $name;

    #[Column(name: "created_at", type: "datetime", nullable: false)]
    public \DateTime $createdAt;

    #[Column(name: "updated_at", type: "datetime", nullable: false)]
    public \DateTime $updatedAt;

    public function __construct() {
        $this->createdAt = new \DateTime('now');
        $this->updatedAt = new \DateTime('now');
    }

    public function jsonSerialize(): array {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'createdAt' => $this->createdAt->format('Y-m-d H:i:s'),
            'updatedAt' => $this->updatedAt->format('Y-m-d H:i:s')
        ];
    }
}
