<?php

declare(strict_types=1);

namespace dev\suvera\exms\admin\data;

use dev\winterframework\stereotype\JsonProperty;

class StudentUpdateForm {
    #[JsonProperty(required: true, validate: [['len', 'max' => 64]])]
    public string $name;

    #[JsonProperty(required: true, validate: ['email', ['len', 'max' => 128]])]
    public string $email;

    #[JsonProperty(required: true)]
    public array $classes = [];
}
