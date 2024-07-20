<?php

declare(strict_types=1);

namespace dev\suvera\exms\views;

use dev\winterframework\web\view\HtmlTemplate;

class LoginTemplate extends HtmlTemplate {

    public function __construct(array $models = []) {
        if (!isset($models['pageTitle'])) {
            $models['pageTitle'] = 'Student Login';
        }
        parent::__construct(
            __DIR__ . '/files/header.phtml',
            __DIR__ . '/files/footer.phtml',
            __DIR__ . '/files/student_login/login.phtml',
            $models
        );
    }
}