<?php

declare(strict_types=1);

namespace dev\suvera\exms\admin\rest;

use dev\suvera\exms\admin\data\SubjectCreateForm;
use dev\suvera\exms\admin\service\SubjectService;
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
class SubjectController extends AdminController {

    #[Autowired]
    protected SubjectService $service;

    #[PostMapping(path: '/admin/subject', consumes: [MediaType::APPLICATION_JSON], produces: [MediaType::APPLICATION_JSON])]
    public function createStudent(
        #[RequestBody] SubjectCreateForm $form
    ): ResponseEntity {
        $student = $this->service->create($form);
        return ResponseEntity::ok(
            ResponseEntity::defaultBody([
                'message' => 'Subject created successfully'
            ])
        );
    }

    #[PatchMapping(path: '/admin/subject/{id}', consumes: [MediaType::APPLICATION_JSON], produces: [MediaType::APPLICATION_JSON])]
    public function updateStudent(
        #[PathVariable(name: 'id')] int $id,
        #[RequestBody] SubjectCreateForm $form
    ): ResponseEntity {
        $student = $this->service->update($id, $form);
        return ResponseEntity::ok(
            ResponseEntity::defaultBody([
                'message' => 'Subject updated successfully'
            ])
        );
    }

    #[GetMapping(path: '/admin/subject/{id}', produces: [MediaType::APPLICATION_JSON])]
    public function getStudent(
        #[PathVariable(name: 'id')] int $id
    ): ResponseEntity {
        $student = $this->service->getOne($id);
        return ResponseEntity::ok(
            ResponseEntity::defaultBody([
                'message' => 'Subject retrieved successfully',
                'data' => $student
            ])
        );
    }

    #[GetMapping(path: '/admin/subject', produces: [MediaType::APPLICATION_JSON])]
    public function getStudents(
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
                'message' => 'Subjects retrieved successfully',
                'count' => count($paginator),
                'offset' => $offset,
                'limit' => $limit,
                'data' => $items
            ])
        );
    }
}
