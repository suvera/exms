<?php

declare(strict_types=1);

namespace dev\suvera\exms\adminui\controller;

use dev\suvera\exms\admin\service\AdminLoginService;
use dev\winterframework\core\web\ControllerInterceptor;
use dev\winterframework\stereotype\Autowired;
use dev\winterframework\web\http\HttpRequest;
use dev\winterframework\web\http\HttpStatus;
use dev\winterframework\web\http\ResponseEntity;

abstract class AdminController implements ControllerInterceptor {

    #[Autowired]
    public AdminLoginService $adminLoginService;

    public function preHandle(HttpRequest $request, ResponseEntity $response, \ReflectionMethod $handler): bool {
        $student = $this->adminLoginService->sessionLogin($request);
        if (!$student) {
            $response->withStatus(HttpStatus::$UNAUTHORIZED)
                ->setBody($response->getUnauthorizedBody('Authentication required, Invalid session'));
            return false;
        }

        return true;
    }

    public function postHandle(HttpRequest $request, ResponseEntity $response, \ReflectionMethod $handler): void {
    }
}
