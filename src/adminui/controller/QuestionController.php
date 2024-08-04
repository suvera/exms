<?php

namespace dev\suvera\exms\adminui\controller;

use dev\suvera\exms\adminui\controller\AdminController;
use dev\suvera\exms\adminui\view\AdminTemplate;
use dev\winterframework\stereotype\RestController;
use dev\winterframework\stereotype\web\GetMapping;
use dev\winterframework\web\view\HtmlTemplateView;
use dev\winterframework\web\view\View;

#[RestController()]
class QuestionController extends AdminController {
    #[GetMapping(path: "/admin/home")]
    public function home(): View {

        $tpl = new AdminTemplate('home', [
            'pageTitle' => 'Admin Home'
        ]);

        return new HtmlTemplateView($tpl);
    }
}
