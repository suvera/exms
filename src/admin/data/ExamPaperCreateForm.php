<?php

declare(strict_types=1);

namespace dev\suvera\exms\admin\data;

use dev\suvera\exms\data\ExamPaperStatus;
use dev\winterframework\stereotype\JsonProperty;

class ExamPaperCreateForm {
    #[JsonProperty(required: true, validate: [['len', 'max' => 255]])]
    public string $name;

    #[JsonProperty(name: "subject_id", required: true, validate: [['int', 'min' => 1]])]
    public int $subjectId;

    #[JsonProperty(required: true)]
    public array $classes = [];

    #[JsonProperty(required: true, name: "exam_time", validate: [['int', 'min' => 1]])]
    public int $totalTimeMins = 0;

    #[JsonProperty(required: true, validate: [['oneOf', 'values' => ['preparing', 'freezed']]])]
    public string $status;
}
