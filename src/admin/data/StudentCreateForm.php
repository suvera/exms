<?php

declare(strict_types=1);

namespace dev\suvera\exms\admin\data;

use dev\winterframework\stereotype\JsonProperty;

class StudentCreateForm {
    #[JsonProperty(required: true, validate: [['len', 'max' => 64]])]
    public string $name;

    #[JsonProperty(required: true, validate: ['username', ['len', 'max' => 64]])]
    public string $username;

    #[JsonProperty(required: true, validate: ['email', ['len', 'max' => 128]])]
    public string $email;

    #[JsonProperty(required: true, validate: ['password', ['len', 'min' => 8, 'max' => 24]])]
    public string $password;

    #[JsonProperty(required: false)]
    public ?array $classes = null;
}
