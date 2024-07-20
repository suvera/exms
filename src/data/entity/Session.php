<?php

namespace dev\suvera\exms\data\entity;

use dev\winterframework\bombok\Data;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Id;

#[Entity]
#[Table(name: "sessions")]
class Session {
    use Data;

    #[Id]
    #[Column(name: "session_id", type: "string", length: 128, nullable: false)]
    public string $id;

    #[Column(name: "username", type: "string", nullable: false, length: 255)]
    public string $username;

    #[Column(name: "session_data", type: "string", nullable: true)]
    public ?string $data;

    #[Column(name: "session_type", type: "integer", nullable: false)]
    public int $type = 0;

    #[Column(name: "session_expires", type: "datetime", nullable: false)]
    public \DateTime $expires;

    #[Column(name: "created_at", type: "datetime", nullable: false)]
    public \DateTime $createdAt;

    #[Column(name: "updated_at", type: "datetime", nullable: false)]
    public \DateTime $updatedAt;

    public function __construct() {
        $this->createdAt = new \DateTime('now');
        $this->updatedAt = new \DateTime('now');
        $this->expires = (new \DateTime('now'))->add(new \DateInterval('PT1H'));
    }
}
