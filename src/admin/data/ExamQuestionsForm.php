<?php

declare(strict_types=1);

namespace dev\suvera\exms\admin\data;

use dev\winterframework\stereotype\JsonProperty;

class ExamQuestionsForm {
    /** @var ExamQuestionForm[] */
    #[JsonProperty(required: true, listClass: ExamQuestionForm::class)]
    public array $questions;
}
