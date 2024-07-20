<?php

declare(strict_types=1);

namespace dev\suvera\exms\admin\data;

use dev\winterframework\stereotype\JsonProperty;

class QuestionOptionForm {
    #[JsonProperty(name: "a", required: true)]
    public string $choiceA;

    #[JsonProperty(name: "b", required: true)]
    public string $choiceB;

    #[JsonProperty(name: "c", required: true)]
    public string $choiceC;

    #[JsonProperty(name: "d", required: true)]
    public string $choiceD;
}
