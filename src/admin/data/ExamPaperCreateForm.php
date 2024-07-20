<?php

declare(strict_types=1);

namespace dev\suvera\exms\admin\data;

use dev\winterframework\stereotype\JsonProperty;

class ExamPaperCreateForm {
    #[JsonProperty(required: true, validate: [['len', 'max' => 255]])]
    public string $name;

    #[JsonProperty(name: "subject_id", required: true, validate: [['int', 'min' => 1]])]
    public int $subjectId;

    #[JsonProperty(required: false)]
    public ?array $classes = null;
}
