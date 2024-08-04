<?php

namespace dev\suvera\exms\adminui\view;

use dev\winterframework\web\view\HtmlTemplate;

class LoginTemplate extends HtmlTemplate {

    public function __construct(array $models = []) {
        if (!isset($models['pageTitle'])) {
            $models['pageTitle'] = 'Admin Login';
        }
        parent::__construct(
            __DIR__ . '/files/header.phtml',
            __DIR__ . '/files/footer.phtml',
            __DIR__ . '/files/login/login.phtml',
            $models
        );
    }
}
