<?php

namespace dev\suvera\exms\adminui\view;

use dev\winterframework\web\view\HtmlTemplate;

class AdminTemplate extends HtmlTemplate {

    public function __construct(string $view, array $models = []) {
        if (!isset($models['pageTitle'])) {
            $models['pageTitle'] = 'Admin';
        }
        parent::__construct(
            __DIR__ . '/files/simple/header.phtml',
            __DIR__ . '/files/simple/footer.phtml',
            __DIR__ . '/files/simple/' . $view . '.phtml',
            $models
        );
    }
}
