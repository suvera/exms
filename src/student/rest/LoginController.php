<?php

declare(strict_types=1);

namespace dev\suvera\exms\student\rest;

use dev\suvera\exms\student\service\LoginService;
use dev\winterframework\stereotype\Autowired;
use dev\winterframework\stereotype\RestController;
use dev\winterframework\stereotype\web\PostMapping;
use dev\winterframework\stereotype\web\RequestParam;
use dev\winterframework\web\http\HttpRequest;
use dev\winterframework\web\http\ResponseEntity;

#[RestController]
class LoginController extends BaseController {

    #[Autowired()]
    public LoginService $loginService;

    #[PostMapping(path: '/student/login')]
    public function login(
        #[RequestParam(source: "post", required: true)] string $username,
        #[RequestParam(source: "post", required: true)] string $password
    ): ResponseEntity {
        $student = $this->loginService->loginInitSession($username, $password);

        if ($student === null) {
            return ResponseEntity::unauthorized()->setBody(
                ResponseEntity::getUnauthorizedBody('Invalid username or password')
            );
        }

        return ResponseEntity::ok(
            ResponseEntity::defaultBody([
                'message' => 'Login successful',
                'token' => session_id()
            ])
        );
    }

    #[PostMapping(path: '/student/logout')]
    public function logout(HttpRequest $request): ResponseEntity {
        $this->loginService->sessionLogout($request);

        return ResponseEntity::ok(
            ResponseEntity::defaultBody([
                'message' => 'Logout successful'
            ])
        );
    }
}
