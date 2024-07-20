<?php

namespace dev\suvera\exms\data\entity;

use dev\suvera\exms\data\ExamPaperStatus;
use dev\suvera\exms\utils\MyCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Ramsey\Uuid\Uuid;

#[Entity]
#[Table(name: "exam_paper")]
class ExamPaper {
    #[Id]
    #[GeneratedValue]
    #[Column(name: "id", type: "integer", nullable: false)]
    public int $id;

    #[Column(name: "uuid", type: "string", length: 36, nullable: false, unique: true)]
    public string $uuid;

    #[Column(name: "subject_id", type: "integer", nullable: false)]
    public int $subjectId;

    #[Column(name: "name", type: "string", nullable: false, length: 255)]
    public string $name;

    #[Column(name: "status", type: "string", nullable: false, enumType: ExamPaperStatus::class)]
    public ExamPaperStatus $status = ExamPaperStatus::PREPARING;

    #[Column(name: "total_questions", type: "integer", nullable: false)]
    public int $totalQuestions = 0;

    #[Column(name: "total_time_mins", type: "integer", nullable: false)]
    public int $totalTimeMins = 0;

    #[Column(name: "created_at", type: "datetime", nullable: false)]
    public \DateTime $createdAt;

    #[Column(name: "updated_at", type: "datetime", nullable: false)]
    public \DateTime $updatedAt;

    #[ManyToOne(targetEntity: Subject::class)]
    #[JoinColumn(name: 'subject_id', referencedColumnName: 'id')]
    public ?Subject $subject = null;

    #[OneToMany(targetEntity: ExamPaperClass::class, mappedBy: 'examPaper')]
    public Collection $classes;

    public function __construct() {
        $this->classes = new MyCollection();
        $this->createdAt = new \DateTime('now');
        $this->updatedAt = new \DateTime('now');
        $this->uuid = Uuid::uuid4()->toString();
    }
}
