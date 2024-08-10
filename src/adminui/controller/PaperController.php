<?php

namespace dev\suvera\exms\adminui\controller;

use dev\suvera\exms\admin\service\ExamPaperService;
use dev\suvera\exms\admin\service\SubjectService;
use dev\suvera\exms\adminui\controller\AdminController;
use dev\suvera\exms\adminui\view\AdminTemplate;
use dev\suvera\exms\data\ClassData;
use dev\winterframework\enums\RequestMethod;
use dev\winterframework\stereotype\Autowired;
use dev\winterframework\stereotype\RestController;
use dev\winterframework\stereotype\web\GetMapping;
use dev\winterframework\stereotype\web\PathVariable;
use dev\winterframework\stereotype\web\RequestMapping;
use dev\winterframework\stereotype\web\RequestParam;
use dev\winterframework\web\view\HtmlTemplateView;
use dev\winterframework\web\view\View;
use Throwable;

#[RestController()]
class PaperController extends AdminController {

    #[Autowired]
    protected SubjectService $subjectSvc;

    #[Autowired]
    protected ExamPaperService $paperSvc;

    #[GetMapping(path: "/admin/ui/exam_paper")]
    public function home(
        #[RequestParam(required: false, source: 'get')] int $page = 0,
        #[RequestParam(required: false, source: 'get')] string $search = '',
    ): View {
        if ($page < 0) {
            return $this->errorPage('Page number value cannot be negative');
        }
        $data = [
            'pageTitle' => 'Exam Papers',
            'pageNum' => $page,
            'search' => $search,
            'pageSize' => self::PAGE_SIZE
        ];

        $offset = $page * self::PAGE_SIZE;
        $limit = self::PAGE_SIZE;
        $data['papers'] = $this->paperSvc->getList($offset, $limit, $search);

        $tpl = new AdminTemplate($this->ctx, 'exam_paper/list', $data);

        return new HtmlTemplateView($tpl);
    }

    #[RequestMapping(path: "/admin/ui/exam_paper/generate", method: [RequestMethod::GET, RequestMethod::POST])]
    public function generate(): View {
        $model = [
            'pageTitle' => 'Exam Paper - Generate',
        ];

        $model['subjects'] = $this->subjectSvc->getAll();
        $model['classes'] = ClassData::getClassIdNames();

        $tpl = new AdminTemplate($this->ctx, 'exam_paper/generate_ai', $model);

        return new HtmlTemplateView($tpl);
    }

    #[GetMapping(path: "/admin/ui/exam_paper/{id}")]
    public function show(
        #[PathVariable] int $id
    ): View {
        try {
            $paper = $this->paperSvc->getOne($id);
        } catch (\Throwable $ex) {
            return $this->errorPage($ex->getMessage());
        }
        $model = [
            'pageTitle' => 'Exam Paper - ' . $paper->name,
            'paper' => $paper,
            "questions" => $this->paperSvc->getQuestions($paper->id, 0, 2000)
        ];

        $tpl = new AdminTemplate($this->ctx, 'exam_paper/view', $model);
        return new HtmlTemplateView($tpl);
    }

    #[GetMapping(path: "/admin/ui/exam_paper/edit/{id}")]
    public function edit(
        #[PathVariable] int $id
    ): View {
        try {
            $paper = $this->paperSvc->getOne($id);
        } catch (\Throwable $ex) {
            return $this->errorPage($ex->getMessage());
        }
        $model = [
            'pageTitle' => 'Exam Paper - ' . $paper->name,
            'paper' => $paper,
        ];

        $model['subjects'] = $this->subjectSvc->getAll();
        $model['classes'] = ClassData::getClassIdNames();

        $tpl = new AdminTemplate($this->ctx, 'exam_paper/edit', $model);
        return new HtmlTemplateView($tpl);
    }
}
