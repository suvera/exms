<?php

namespace dev\suvera\exms\adminui\controller;

use dev\suvera\exms\adminui\controller\AdminController;
use dev\suvera\exms\adminui\view\AdminTemplate;
use dev\winterframework\enums\RequestMethod;
use dev\winterframework\stereotype\Autowired;
use dev\winterframework\stereotype\RestController;
use dev\winterframework\stereotype\web\GetMapping;
use dev\winterframework\stereotype\web\RequestMapping;
use dev\winterframework\web\view\HtmlTemplateView;
use dev\winterframework\web\view\View;

#[RestController()]
class SubjectController extends AdminController {

    #[RequestMapping(path: "/admin/ui/subject/create", method: [RequestMethod::GET, RequestMethod::POST])]
    public function create(): View {

        $tpl = new AdminTemplate($this->ctx, 'subject/create', [
            'pageTitle' => 'Subject - Create',
        ]);

        return new HtmlTemplateView($tpl);
    }
}
