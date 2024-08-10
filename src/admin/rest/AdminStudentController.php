<?php

declare(strict_types=1);

namespace dev\suvera\exms\admin\rest;

use dev\suvera\exms\admin\data\StudentCreateForm;
use dev\suvera\exms\admin\data\StudentUpdateForm;
use dev\suvera\exms\admin\service\AdminStudentService;
use dev\winterframework\stereotype\Autowired;
use dev\winterframework\stereotype\RestController;
use dev\winterframework\stereotype\web\PatchMapping;
use dev\winterframework\stereotype\web\PathVariable;
use dev\winterframework\stereotype\web\PostMapping;
use dev\winterframework\stereotype\web\RequestBody;
use dev\winterframework\web\http\ResponseEntity;
use dev\winterframework\web\MediaType;

#[RestController]
class AdminStudentController extends AdminController {

    #[Autowired()]
    private AdminStudentService $adminStudentService;

    #[PostMapping(path: '/admin/student', consumes: [MediaType::APPLICATION_JSON], produces: [MediaType::APPLICATION_JSON])]
    public function createStudent(
        #[RequestBody] StudentCreateForm $studentForm
    ): ResponseEntity {
        $student = $this->adminStudentService->createStudent($studentForm);
        return ResponseEntity::ok(
            ResponseEntity::defaultBody([
                "username" => $student->username,
                "password" => $studentForm->password,
                'message' => 'Student created successfully'
            ])
        );
    }

    #[PatchMapping(path: '/admin/student/{id}', consumes: [MediaType::APPLICATION_JSON], produces: [MediaType::APPLICATION_JSON])]
    public function updateStudent(
        #[PathVariable()] int $id,
        #[RequestBody] StudentUpdateForm $studentForm
    ): ResponseEntity {
        $student = $this->adminStudentService->updateStudent($id, $studentForm);
        return ResponseEntity::ok(
            ResponseEntity::defaultBody([
                "id" => $student->id,
                'message' => 'Student updated successfully'
            ])
        );
    }
}
