<?php

declare(strict_types=1);

namespace dev\suvera\exms\admin\data;

use dev\winterframework\stereotype\JsonProperty;

class SubjectCreateForm {
    #[JsonProperty(required: true, validate: [['len', 'max' => 255]])]
    public string $name;
}
