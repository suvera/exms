<?php

declare(strict_types=1);

namespace dev\suvera\exms\rest;

use dev\winterframework\stereotype\RestController;
use dev\winterframework\stereotype\web\GetMapping;
use dev\winterframework\web\view\HtmlTemplateView;
use dev\winterframework\web\view\View;
use dev\suvera\exms\views\LoginTemplate;
use dev\winterframework\web\http\HttpRequest;

#[RestController]
class IndexController {

    #[GetMapping(path: "/home")]
    public function statdentLogin(HttpRequest $request): View {
        $tpl = new LoginTemplate([
            'request' => $request,
            'pageTitle' => 'Student Login'
        ]);

        return new HtmlTemplateView($tpl);
    }
}
