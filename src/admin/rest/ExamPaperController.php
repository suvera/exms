<?php

declare(strict_types=1);

namespace dev\suvera\exms\admin\rest;

use dev\suvera\exms\admin\data\ExamPaperCreateForm;
use dev\suvera\exms\admin\service\ExamPaperService;
use dev\winterframework\stereotype\Autowired;
use dev\winterframework\stereotype\RestController;
use dev\winterframework\stereotype\web\GetMapping;
use dev\winterframework\stereotype\web\PatchMapping;
use dev\winterframework\stereotype\web\PathVariable;
use dev\winterframework\stereotype\web\PostMapping;
use dev\winterframework\stereotype\web\RequestBody;
use dev\winterframework\stereotype\web\RequestParam;
use dev\winterframework\web\http\ResponseEntity;
use dev\winterframework\web\MediaType;

#[RestController]
class ExamPaperController extends AdminController {

    #[Autowired]
    protected ExamPaperService $service;

    #[PostMapping(path: '/admin/exam_paper', consumes: [MediaType::APPLICATION_JSON], produces: [MediaType::APPLICATION_JSON])]
    public function createExamPaper(
        #[RequestBody] ExamPaperCreateForm $form
    ): ResponseEntity {
        $paper = $this->service->create($form);
        return ResponseEntity::ok(
            ResponseEntity::defaultBody([
                'message' => 'ExamPaper created successfully',
                'id' => $paper->id
            ])
        );
    }

    #[GetMapping(path: '/admin/exam_paper/{paper_id}', produces: [MediaType::APPLICATION_JSON])]
    public function getExamPaper(
        #[PathVariable(name: 'paper_id')] int $id
    ): ResponseEntity {
        $paper = $this->service->getOne($id);
        return ResponseEntity::ok(
            ResponseEntity::defaultBody([
                'message' => 'ExamPaper retrieved successfully',
                'data' => $paper
            ])
        );
    }

    #[PatchMapping(path: '/admin/exam_paper/{paper_id}', consumes: [MediaType::APPLICATION_JSON], produces: [MediaType::APPLICATION_JSON])]
    public function updateExamPaper(
        #[PathVariable(name: 'paper_id')] int $id,
        #[RequestBody] ExamPaperCreateForm $form
    ): ResponseEntity {
        $paper = $this->service->update($id, $form);
        return ResponseEntity::ok(
            ResponseEntity::defaultBody([
                'message' => 'ExamPaper updated successfully'
            ])
        );
    }

    #[GetMapping(path: '/admin/exam_paper', produces: [MediaType::APPLICATION_JSON])]
    public function getExamPapers(
        #[RequestParam(required: false)] int $offset = 0,
        #[RequestParam(required: false)] int $limit = 10,
    ): ResponseEntity {
        $paginator = $this->service->getList($offset, $limit);

        $items = [];
        foreach ($paginator as $item) {
            $items[] = $item;
        }

        return ResponseEntity::ok(
            ResponseEntity::defaultBody([
                'message' => 'ExamPapers retrieved successfully',
                'count' => count($paginator),
                'offset' => $offset,
                'limit' => $limit,
                'data' => $items
            ])
        );
    }

    #[PostMapping(path: '/admin/exam_paper/{paper_id}/freeze', produces: [MediaType::APPLICATION_JSON])]
    public function freezeQuestionPaper(
        #[PathVariable(name: 'paper_id')] int $paperId
    ): ResponseEntity {
        $this->service->freezePaper($paperId);
        return ResponseEntity::ok(
            ResponseEntity::defaultBody([
                'message' => 'Paper frozen successfully'
            ])
        );
    }
}
