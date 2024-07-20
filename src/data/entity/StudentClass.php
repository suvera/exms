<?php

namespace dev\suvera\exms\data\entity;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;

#[Entity]
#[Table(name: "student_class")]
class StudentClass {
    #[Id]
    #[GeneratedValue]
    #[Column(name: "id", type: "integer", nullable: false)]
    public int $id;

    #[Column(name: "student_id", type: "integer", nullable: false)]
    public int $studentId;

    #[Column(name: "class_id", type: "string", nullable: false, length: 255)]
    public string $classId;

    #[ManyToOne(targetEntity: Student::class, inversedBy: 'classes')]
    #[JoinColumn(name: 'student_id', referencedColumnName: 'id')]
    public ?Student $student = null;

    public function __construct() {
    }
}
