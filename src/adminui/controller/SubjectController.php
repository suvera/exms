<?php

namespace dev\suvera\exms\adminui\controller;

use dev\suvera\exms\admin\service\SubjectService;
use dev\suvera\exms\adminui\controller\AdminController;
use dev\suvera\exms\adminui\view\AdminTemplate;
use dev\winterframework\stereotype\Autowired;
use dev\winterframework\stereotype\RestController;
use dev\winterframework\stereotype\web\GetMapping;
use dev\winterframework\stereotype\web\PathVariable;
use dev\winterframework\stereotype\web\RequestParam;
use dev\winterframework\web\view\HtmlTemplateView;
use dev\winterframework\web\view\View;

#[RestController()]
class SubjectController extends AdminController {

    #[Autowired()]
    protected SubjectService $subjectSvc;

    #[GetMapping(path: "/admin/ui/subject")]
    public function home(
        #[RequestParam(required: false, source: 'get')] int $page = 0,
        #[RequestParam(required: false, source: 'get')] string $search = '',
    ): View {
        if ($page < 0) {
            return $this->errorPage('Page number value cannot be negative');
        }
        $data = [
            'pageTitle' => 'Subjects',
            'pageNum' => $page,
            'search' => $search,
            'pageSize' => self::PAGE_SIZE
        ];

        $offset = $page * self::PAGE_SIZE;
        $limit = self::PAGE_SIZE;
        $data['subjects'] = $this->subjectSvc->getList($offset, $limit, $search);

        $tpl = new AdminTemplate($this->ctx, 'subject/list', $data);

        return new HtmlTemplateView($tpl);
    }


    #[GetMapping(path: "/admin/ui/subject/create")]
    public function create(): View {

        $tpl = new AdminTemplate($this->ctx, 'subject/create', [
            'pageTitle' => 'Subjects',
        ]);

        return new HtmlTemplateView($tpl);
    }

    #[GetMapping(path: "/admin/ui/subject/edit/{id}")]
    public function edit(
        #[PathVariable] int $id
    ): View {

        try {
            $subject = $this->subjectSvc->getOne($id);
        } catch (\Throwable $ex) {
            return $this->errorPage($ex->getMessage());
        }

        $tpl = new AdminTemplate($this->ctx, 'subject/create', [
            'pageTitle' => 'Subject - ' . $subject->name,
            'subject' => $subject
        ]);

        return new HtmlTemplateView($tpl);
    }
}
