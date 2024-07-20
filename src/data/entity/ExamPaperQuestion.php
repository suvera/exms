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
#[Table(name: "exam_paper_question")]
class ExamPaperQuestion {
    #[Id]
    #[GeneratedValue]
    #[Column(name: "id", type: "integer", nullable: false)]
    public int $id;

    #[Column(name: "exam_paper_id", type: "integer", nullable: false)]
    public int $examPaperId;

    #[Column(name: "question", type: "text", nullable: false, length: 65535)]
    public string $question;

    #[Column(name: "course_topic", type: "string", nullable: true, length: 1024)]
    public ?string $topic;

    #[Column(name: "time_secs", type: "integer", nullable: false)]
    public int $timeSecs = 0;

    #[Column(name: "answer", type: "string", nullable: true, length: 1)]
    public ?string $answer;

    #[Column(name: "explanation", type: "text", nullable: true, length: 65535)]
    public ?string $explanation;

    #[Column(name: "choice_a", type: "text", nullable: true, length: 65535)]
    public ?string $choiceA;

    #[Column(name: "choice_b", type: "text", nullable: true, length: 65535)]
    public ?string $choiceB;

    #[Column(name: "choice_c", type: "text", nullable: true, length: 65535)]
    public ?string $choiceC;

    #[Column(name: "choice_d", type: "text", nullable: true, length: 65535)]
    public ?string $choiceD;

    #[Column(name: "created_at", type: "datetime", nullable: false)]
    public \DateTime $createdAt;

    #[Column(name: "updated_at", type: "datetime", nullable: false)]
    public \DateTime $updatedAt;

    #[ManyToOne(targetEntity: ExamPaper::class)]
    #[JoinColumn(name: 'exam_paper_id', referencedColumnName: 'id')]
    public ?ExamPaper $examPaper = null;

    public function __construct() {
        $this->createdAt = new \DateTime('now');
        $this->updatedAt = new \DateTime('now');
    }
}
