<?php

declare(strict_types=1);

namespace dev\suvera\exms\admin\rest;

use dev\suvera\exms\admin\data\ExamQuestionsForm;
use dev\suvera\exms\admin\service\ExamPaperService;
use dev\winterframework\stereotype\Autowired;
use dev\winterframework\stereotype\RestController;
use dev\winterframework\stereotype\web\DeleteMapping;
use dev\winterframework\stereotype\web\GetMapping;
use dev\winterframework\stereotype\web\PatchMapping;
use dev\winterframework\stereotype\web\PathVariable;
use dev\winterframework\stereotype\web\RequestBody;
use dev\winterframework\stereotype\web\RequestParam;
use dev\winterframework\web\http\ResponseEntity;
use dev\winterframework\web\MediaType;

#[RestController]
class ExamPaperQuestionController extends AdminController {

    #[Autowired]
    protected ExamPaperService $service;

    #[PatchMapping(path: '/admin/exam_paper/{paper_id}/question', consumes: [MediaType::APPLICATION_JSON], produces: [MediaType::APPLICATION_JSON])]
    public function addQuestions(
        #[PathVariable(name: 'paper_id')] int $paperId,
        #[RequestBody] ExamQuestionsForm $form
    ): ResponseEntity {
        $this->service->addQuestions($paperId, $form);
        return ResponseEntity::ok(
            ResponseEntity::defaultBody([
                'message' => 'Question(s) added successfully'
            ])
        );
    }

    #[GetMapping(path: '/admin/exam_paper/{paper_id}/question/{id}', produces: [MediaType::APPLICATION_JSON])]
    public function getExamPaper(
        #[PathVariable(name: 'paper_id')] int $paperId,
        #[PathVariable(name: 'id')] int $id
    ): ResponseEntity {
        $question = $this->service->getOneQuestion($paperId, $id);
        return ResponseEntity::ok(
            ResponseEntity::defaultBody([
                'message' => 'Question retrieved successfully',
                'data' => $question
            ])
        );
    }

    #[DeleteMapping(path: '/admin/exam_paper/{paper_id}/question/{id}', produces: [MediaType::APPLICATION_JSON])]
    public function deleteExamPaper(
        #[PathVariable(name: 'paper_id')] int $paperId,
        #[PathVariable(name: 'id')] int $id
    ): ResponseEntity {
        $this->service->deleteOneQuestion($paperId, $id);
        return ResponseEntity::ok(
            ResponseEntity::defaultBody([
                'message' => 'Question deleted successfully'
            ])
        );
    }

    #[GetMapping(path: '/admin/exam_paper/{paper_id}/question', produces: [MediaType::APPLICATION_JSON])]
    public function getExamPapers(
        #[PathVariable(name: 'paper_id')] int $paperId,
        #[RequestParam(required: false)] int $offset = 0,
        #[RequestParam(required: false)] int $limit = 10,
    ): ResponseEntity {
        $paginator = $this->service->getQuestions($paperId, $offset, $limit);

        $items = [];
        foreach ($paginator as $item) {
            $items[] = $item;
        }

        return ResponseEntity::ok(
            ResponseEntity::defaultBody([
                'message' => 'Question(s) retrieved successfully',
                'count' => count($paginator),
                'offset' => $offset,
                'limit' => $limit,
                'data' => $items
            ])
        );
    }
}
