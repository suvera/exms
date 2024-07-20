<?php

namespace dev\suvera\exms\data\entity;

use dev\winterframework\bombok\Data;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;

#[Entity]
#[Table(name: "admin")]
class Admin {
    use Data;

    #[Id]
    #[GeneratedValue]
    #[Column(name: "id", type: "integer", nullable: false)]
    public int $id;

    #[Column(name: "name", type: "string", nullable: false, length: 255)]
    public string $name;

    #[Column(name: "username", type: "string", nullable: false, unique: true, updatable: false, length: 255)]
    public string $username;

    #[Column(name: "password", type: "string", nullable: false, length: 255)]
    public string $password;

    #[Column(name: "created_at", type: "datetime", nullable: false)]
    public \DateTime $createdAt;

    #[Column(name: "updated_at", type: "datetime", nullable: false)]
    public \DateTime $updatedAt;

    public function __construct() {
        $this->createdAt = new \DateTime('now');
        $this->updatedAt = new \DateTime('now');
    }
}
