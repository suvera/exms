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

    #[GetMapping(path: "student/login")]
    public function statdentLogin(HttpRequest $request): View {
        $tpl = new LoginTemplate([
            'request' => $request,
            'pageTitle' => 'Student Login'
        ]);

        return new HtmlTemplateView($tpl);
    }

    #[GetMapping(path: "/paper/new")]
    public function newPaper(HttpRequest $request, #[RequestParam(source: "get", required: false)] string $error = ''): View {
        $tpl = new NewPaperTemplate([
            'request' => $request,
            'pageTitle' => 'New Paper',
            "error" => $error,
        ]);

        return new HtmlTemplateView($tpl);
    }

    #[PostMapping(path: "/paper/new")]
    public function addNewPaper(HttpRequest $request, #[RequestParam(source: "post")] string $paperName): ResponseEntity {
        $paperName = trim($paperName);
        $resp = ResponseEntity::movedAway();
        if (empty($paperName)) {
            $resp->getHeaders()->setLocation('/exms/paper/new?error=Paper Name is Empty');
        } else {
            $paperId = $this->paperSvc->createPaper($paperName);
            $resp->getHeaders()->setLocation('/exms/paper/add_questions?paperId=' . strval($paperId));
        }
        return $resp;
    }

    #[RequestMapping(path: "/paper/add_questions", method: [RequestMethod::GET, RequestMethod::POST])]
    public function addQuestions(
        HttpRequest $request,
        #[RequestParam(source: "get")] int $paperId,
        #[RequestParam(source: "get", required: false)] string $error = ''
    ): View {
        $paper = $this->paperSvc->getPaper($paperId);
        if (empty($paper)) {
            return $this->newPaper($request, 'Paper Not Found');
        }

        if ($request->getMethod() === RequestMethod::POST) {
            $questionJson = $request->getPostParam('questions');

            if (empty($questionJson) && is_string($questionJson)) {
                $error = 'Questions are Empty';
            } else {
                try {
                    $questions = json_decode($questionJson, true, 512, JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    $error = 'Invalid Questions: ' . $e->getMessage();
                }
                if (empty($questions)) {
                    $error = 'Empty Questions';
                } else {
                    $error = $this->paperSvc->addQuestions($paperId, $questions);
                    $paper = $this->paperSvc->getPaper($paperId);
                }
            }
        }

        $models = [
            'request' => $request,
            'pageTitle' => 'Add Questions to the Paper',
            'paperId' => $paperId,
            'error' => $error,
            'paperName' => $paper['name'],
            'totalQuestions' => count($paper['questions']),
        ];

        $tpl = new NewPaperTemplate($models);
        $tpl->setContentFile("add_questions");

        return new HtmlTemplateView($tpl);
    }

    #[RequestMapping(path: "/paper/view", method: [RequestMethod::GET])]
    public function viewPaper(
        HttpRequest $request,
        #[RequestParam(source: "get")] int $paperId,
        #[RequestParam(source: "get", required: false)] int $withAnswers = 0
    ): View {
        $paper = $this->paperSvc->getPaper($paperId);
        if (empty($paper)) {
            return $this->newPaper($request, 'Paper Not Found');
        }

        $totalTimeSecs = 0;
        foreach ($paper['questions'] as $question) {
            $totalTimeSecs += intval($question['possible_solution_time_seconds']);
        }

        $models = [
            'request' => $request,
            'pageTitle' => $paper['name'],
            'paperId' => $paperId,
            'paperName' => $paper['name'],
            'questions' => $paper['questions'],
            'withAnswers' => $withAnswers,
            'timeMins' => 5 + ceil($totalTimeSecs / 60),
        ];

        $tpl = new NewPaperTemplate($models);
        $tpl->setContentFile("paper");

        return new HtmlTemplateView($tpl);
    }
}
