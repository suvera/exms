<?php

declare(strict_types=1);

namespace dev\suvera\exms\rest;

use dev\suvera\exms\service\PaperService;
use dev\winterframework\stereotype\RestController;
use dev\winterframework\stereotype\web\GetMapping;
use dev\winterframework\web\view\HtmlTemplateView;
use dev\winterframework\web\view\View;
use dev\suvera\exms\views\LoginTemplate;
use dev\suvera\exms\views\NewPaperTemplate;
use dev\winterframework\enums\RequestMethod;
use dev\winterframework\stereotype\Autowired;
use dev\winterframework\stereotype\web\PostMapping;
use dev\winterframework\stereotype\web\RequestMapping;
use dev\winterframework\stereotype\web\RequestParam;
use dev\winterframework\web\http\HttpRequest;
use dev\winterframework\web\http\ResponseEntity;

#[RestController]
class IndexController {

    #[Autowired]
    private PaperService $paperSvc;

    #[GetMapping(path: "/home")]
    public function statdentLogin(HttpRequest $request): View {
        $tpl = new LoginTemplate([
            'request' => $request,
            'pageTitle' => 'Student Login'
        ]);

        return new HtmlTemplateView($tpl);
    }
}
