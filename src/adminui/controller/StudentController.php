<?php

namespace dev\suvera\exms\adminui\controller;

use dev\suvera\exms\admin\service\AdminStudentService;
use dev\suvera\exms\admin\service\StudentService;
use dev\suvera\exms\adminui\controller\AdminController;
use dev\suvera\exms\adminui\view\AdminTemplate;
use dev\suvera\exms\data\ClassData;
use dev\winterframework\stereotype\Autowired;
use dev\winterframework\stereotype\RestController;
use dev\winterframework\stereotype\web\GetMapping;
use dev\winterframework\stereotype\web\PathVariable;
use dev\winterframework\stereotype\web\RequestParam;
use dev\winterframework\web\view\HtmlTemplateView;
use dev\winterframework\web\view\View;

#[RestController()]
class StudentController extends AdminController {

    #[Autowired()]
    protected AdminStudentService $studentSvc;

    #[GetMapping(path: "/admin/ui/student")]
    public function home(
        #[RequestParam(required: false, source: 'get')] int $page = 0,
        #[RequestParam(required: false, source: 'get')] string $search = '',
    ): View {
        if ($page < 0) {
            return $this->errorPage('Page number value cannot be negative');
        }
        $data = [
            'pageTitle' => 'Students',
            'pageNum' => $page,
            'search' => $search,
            'pageSize' => self::PAGE_SIZE
        ];

        $offset = $page * self::PAGE_SIZE;
        $limit = self::PAGE_SIZE;
        $data['students'] = $this->studentSvc->getList($offset, $limit, $search);

        $tpl = new AdminTemplate($this->ctx, 'student/list', $data);

        return new HtmlTemplateView($tpl);
    }


    #[GetMapping(path: "/admin/ui/student/create")]
    public function create(): View {

        $tpl = new AdminTemplate($this->ctx, 'student/create', [
            'pageTitle' => 'Students',
            'classes' => ClassData::getClassIdNames()
        ]);

        return new HtmlTemplateView($tpl);
    }

    #[GetMapping(path: "/admin/ui/student/edit/{id}")]
    public function edit(
        #[PathVariable] int $id
    ): View {

        try {
            $student = $this->studentSvc->getOne($id);
        } catch (\Throwable $ex) {
            return $this->errorPage($ex->getMessage());
        }

        $tpl = new AdminTemplate($this->ctx, 'student/edit', [
            'pageTitle' => 'Student - ' . $student->name,
            'student' => $student,
            'classes' => ClassData::getClassIdNames()
        ]);

        return new HtmlTemplateView($tpl);
    }

    #[GetMapping(path: "/admin/ui/student/{id}")]
    public function view(
        #[PathVariable] int $id
    ): View {

        try {
            $student = $this->studentSvc->getOne($id);
        } catch (\Throwable $ex) {
            return $this->errorPage($ex->getMessage());
        }

        $tpl = new AdminTemplate($this->ctx, 'student/view', [
            'pageTitle' => 'Student - ' . $student->name,
            'student' => $student
        ]);

        return new HtmlTemplateView($tpl);
    }
}
