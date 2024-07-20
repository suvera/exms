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
#[Table(name: "exam_paper_class")]
class ExamPaperClass implements \JsonSerializable {
    #[Id]
    #[GeneratedValue]
    #[Column(name: "id", type: "integer", nullable: false)]
    public int $id;

    #[Column(name: "exam_paper_id", type: "integer", nullable: false)]
    public int $examPaperId;

    #[Column(name: "class_id", type: "string", nullable: false, length: 255)]
    public string $classId;

    #[ManyToOne(targetEntity: ExamPaper::class, inversedBy: 'classes')]
    #[JoinColumn(name: 'exam_paper_id', referencedColumnName: 'id')]
    public ?ExamPaper $examPaper = null;

    public function __construct() {
    }

    public function jsonSerialize(): mixed {
        return [
            'id' => $this->id,
            'class_id' => $this->classId,
            'exam_paper_id' => $this->examPaperId
        ];
    }
}
