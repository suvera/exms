<?php

namespace dev\suvera\exms\admin\data;

use dev\winterframework\stereotype\JsonProperty;

class ExamPaperGenerationForm {
    #[JsonProperty(required: true, validate: [['len', 'max' => 255]])]
    public string $name;

    #[JsonProperty(name: "subject_id", required: true, validate: [['int', 'min' => 1]])]
    public int $subjectId;

    #[JsonProperty(required: true)]
    public array $classes = [];

    #[JsonProperty(required: true, validate: [['int', 'min' => 1]])]
    public int $total;

    #[JsonProperty(required: true)]
    public array $topics = [];

    #[JsonProperty(required: true, name: "api_token")]
    public string $apiToken = '';

    #[JsonProperty(required: false)]
    public ?array $chapters = null;
}
