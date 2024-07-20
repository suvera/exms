<?php

declare(strict_types=1);

namespace dev\suvera\exms\student\rest;

use dev\suvera\exms\student\service\LoginService;
use dev\winterframework\stereotype\Autowired;
use dev\winterframework\stereotype\RestController;
use dev\winterframework\stereotype\web\GetMapping;
use dev\winterframework\stereotype\web\PostMapping;
use dev\winterframework\stereotype\web\RequestParam;
use dev\winterframework\web\http\HttpRequest;
use dev\winterframework\web\http\ResponseEntity;

#[RestController]
class InfoController extends StudentController {

    #[GetMapping(path: '/student/info')]
    public function info(): ResponseEntity {

        if (!isset($_SESSION['student']) || empty($_SESSION['student'])) {
            return ResponseEntity::unauthorized()->setBody(
                ResponseEntity::getUnauthorizedBody('Authentication required, Invalid token')
            );
        }

        return ResponseEntity::ok(
            ResponseEntity::defaultBody([
                'message' => 'User Information retrieved successfully',
                'username' => $_SESSION['student']['username'],
                'name' => $_SESSION['student']['name'],
            ])
        );
    }
}
