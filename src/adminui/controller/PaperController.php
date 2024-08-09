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
class PaperController extends AdminController {

    #[GetMapping(path: "/admin/ui/exam_paper")]
    public function home(): View {

        $tpl = new AdminTemplate($this->ctx, 'exam_paper/list', [
            'pageTitle' => 'Exam Papers',
        ]);

        return new HtmlTemplateView($tpl);
    }
    #[RequestMapping(path: "/admin/ui/exam_paper/generate", method: [RequestMethod::GET, RequestMethod::POST])]
    public function generate(): View {
        $model = [
            'pageTitle' => 'Exam Paper - Generate',
        ];

        $tpl = new AdminTemplate($this->ctx, 'exam_paper/generate_ai', $model);

        return new HtmlTemplateView($tpl);
    }
}
