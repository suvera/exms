<?php

declare(strict_types=1);

namespace dev\suvera\exms\student\rest;

use dev\suvera\exms\student\data\QuestionAnswerForm;
use dev\suvera\exms\student\service\ExamService;
use dev\winterframework\exception\HttpRestException;
use dev\winterframework\stereotype\Autowired;
use dev\winterframework\stereotype\RestController;
use dev\winterframework\stereotype\web\GetMapping;
use dev\winterframework\stereotype\web\PatchMapping;
use dev\winterframework\stereotype\web\PathVariable;
use dev\winterframework\stereotype\web\PostMapping;
use dev\winterframework\stereotype\web\RequestBody;
use dev\winterframework\stereotype\web\RequestParam;
use dev\winterframework\web\http\HttpStatus;
use dev\winterframework\web\http\ResponseEntity;
use dev\winterframework\web\MediaType;

#[RestController]
class ExamController extends StudentController {

    #[Autowired]
    protected ExamService $service;

    #[GetMapping(path: '/student/exams/pending', produces: [MediaType::APPLICATION_JSON])]
    public function getPendingExams(
        #[RequestParam(required: false)] int $offset = 0,
        #[RequestParam(required: false)] int $limit = 10,
    ): ResponseEntity {
        $resp = $this->service->getPendingExams($offset, $limit);
        return ResponseEntity::ok($resp);
    }

    #[GetMapping(path: '/student/exams/completed', produces: [MediaType::APPLICATION_JSON])]
    public function getCompletedExams(
        #[RequestParam(required: false)] int $offset = 0,
        #[RequestParam(required: false)] int $limit = 10,
    ): ResponseEntity {
        $resp = $this->service->getCompletedExams($offset, $limit);
        return ResponseEntity::ok($resp);
    }

    #[PostMapping(path: '/student/exam/{uuid}/start', produces: [MediaType::APPLICATION_JSON])]
    public function startExam(
        #[PathVariable(required: true, name: 'uuid')] string $baseExamUuid
    ): ResponseEntity {
        $exam = $this->service->beginExam($baseExamUuid);
        return ResponseEntity::ok([
            ResponseEntity::defaultBody([
                'message' => 'Exam started successfully',
                'examUuid' => $exam->uuid
            ])
        ]);
    }

    #[PostMapping(path: '/student/exam/{uuid}/finish', produces: [MediaType::APPLICATION_JSON])]
    public function finishExam(
        #[PathVariable(required: true, name: 'uuid')] string $examUuid
    ): ResponseEntity {
        $exam = $this->service->finishExam($examUuid);
        return ResponseEntity::ok(
            ResponseEntity::defaultBody([
                'message' => 'Exam finished successfully',
                'examUuid' => $exam->uuid
            ])
        );
    }

    #[GetMapping(path: '/student/exam/{uuid}', produces: [MediaType::APPLICATION_JSON])]
    public function getExam(
        #[PathVariable(required: true, name: 'uuid')] string $examUuid
    ): ResponseEntity {
        $resp = $this->service->getExam($examUuid);
        return ResponseEntity::ok($resp);
    }

    #[PatchMapping(path: '/student/exam/{uuid}', produces: [MediaType::APPLICATION_JSON])]
    public function updateQuestionAnswer(
        #[PathVariable(required: true, name: 'uuid')] string $examUuid,
        #[RequestBody] QuestionAnswerForm $form
    ): ResponseEntity {
        $this->service->updateQuestionAnswer($examUuid, $form->questionId, $form->answer);
        return ResponseEntity::ok([
            ResponseEntity::defaultBody([
                'message' => 'Answer submitted successfully'
            ])
        ]);
    }
}
