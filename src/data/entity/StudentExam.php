<?php

namespace dev\suvera\exms\data\entity;

use dev\suvera\exms\data\StudentExamStatus;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Ramsey\Uuid\Uuid;

#[Entity]
#[Table(name: "student_exam")]
class StudentExam {
    #[Id]
    #[GeneratedValue]
    #[Column(name: "id", type: "integer", nullable: false)]
    public int $id;

    #[Column(name: "uuid", type: "string", length: 36, nullable: false, unique: true)]
    public string $uuid;

    #[Column(name: "student_id", type: "integer", nullable: false)]
    public int $studentId;

    #[Column(name: "exam_paper_id", type: "integer", nullable: false)]
    public int $examPaperId;

    #[Column(name: "status", type: "string", nullable: false, enumType: StudentExamStatus::class)]
    public StudentExamStatus $status = StudentExamStatus::PENDING;

    #[Column(name: "score", type: "integer", nullable: false)]
    public int $score;

    #[Column(name: "total_questions", type: "integer", nullable: false)]
    public int $totalQuestions;

    #[Column(name: "total_time_mins", type: "integer", nullable: false)]
    public int $totalTimeMins;

    #[Column(name: "start_time", type: "datetime", nullable: false)]
    public \DateTime $startTime;

    #[Column(name: "end_time", type: "datetime", nullable: false)]
    public \DateTime $endTime;

    #[Column(name: "created_at", type: "datetime", nullable: false)]
    public \DateTime $createdAt;

    #[Column(name: "updated_at", type: "datetime", nullable: false)]
    public \DateTime $updatedAt;

    public function __construct() {
        $this->startTime = new \DateTime('now');
        $this->endTime = new \DateTime('now');
        $this->createdAt = new \DateTime('now');
        $this->updatedAt = new \DateTime('now');
        $this->uuid = Uuid::uuid4()->toString();
    }
}
