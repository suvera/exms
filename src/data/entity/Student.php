<?php

namespace dev\suvera\exms\data\entity;

use dev\winterframework\bombok\Data;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\OneToMany;

#[Entity]
#[Table(name: "student")]
class Student implements \JsonSerializable {
    use Data;

    #[Id]
    #[GeneratedValue]
    #[Column(name: "id", type: "integer", nullable: false)]
    public int $id;

    #[Column(name: "name", type: "string", nullable: false, length: 255)]
    public string $name;

    #[Column(name: "username", type: "string", nullable: false, length: 255)]
    public string $username;

    #[Column(name: "email", type: "string", nullable: true, length: 255)]
    public ?string $email;

    #[Column(name: "password", type: "string", nullable: false, length: 255)]
    public string $password;

    #[Column(name: "created_at", type: "datetime", nullable: false)]
    public \DateTime $createdAt;

    #[Column(name: "updated_at", type: "datetime", nullable: false)]
    public \DateTime $updatedAt;

    #[Column(name: "failed_attempts", type: "integer", nullable: false)]
    public int $failedAttempts = 0;

    #[Column(name: "last_login", type: "datetime", nullable: true)]
    public ?\DateTime $lastLogin;

    #[OneToMany(targetEntity: StudentClass::class, mappedBy: 'student')]
    public Collection $classes;

    public function __construct() {
        $this->classes = new ArrayCollection();
        $this->createdAt = new \DateTime('now');
        $this->updatedAt = new \DateTime('now');
    }

    public function jsonSerialize(): array {
        return $this->__serialize();
    }

    public function __serialize(): array {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email
        ];
    }

    public function __unserialize(array $data): void {
        $this->id = $data['id'];
        $this->name = $data['name'];
        $this->username = $data['username'];
        $this->email = $data['email'];
    }
}
