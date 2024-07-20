<?php

declare(strict_types=1);

namespace dev\suvera\exms\student\data;

use dev\winterframework\stereotype\JsonProperty;

class QuestionAnswerForm {
    #[JsonProperty(required: true, name: 'question_id', validate: [['int', 'min' => 1]])]
    public int $questionId;

    #[JsonProperty(required: true, validate: [['len', 'min' => 1, 'max' => 1]])]
    public string $answer;
}
