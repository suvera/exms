<?php

namespace dev\suvera\exms\data\entity;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;

#[Entity]
#[Table(name: "student_exam_answer")]
class StudentExamAnswer {
    #[Id]
    #[GeneratedValue]
    #[Column(name: "id", type: "integer", nullable: false)]
    public int $id;

    #[Column(name: "student_exam_id", type: "integer", nullable: false)]
    public int $studentExamId;

    #[Column(name: "exam_paper_question_id", type: "integer", nullable: false)]
    public int $examPaperQuestionId;

    #[Column(name: "answer", type: "string", nullable: true, length: 1)]
    public ?string $answer;

    #[Column(name: "is_correct", type: "boolean", nullable: false)]
    public bool $isCorrect;

    #[Column(name: "created_at", type: "datetime", nullable: false)]
    public \DateTime $createdAt;

    #[Column(name: "updated_at", type: "datetime", nullable: false)]
    public \DateTime $updatedAt;

    public function __construct() {
        $this->createdAt = new \DateTime('now');
        $this->updatedAt = new \DateTime('now');
    }
}
