<?php

declare(strict_types=1);

namespace dev\suvera\exms\admin\rest;

use dev\suvera\exms\admin\service\AdminLoginService;
use dev\winterframework\core\web\ControllerInterceptor;
use dev\winterframework\stereotype\Autowired;
use dev\winterframework\web\http\HttpRequest;
use dev\winterframework\web\http\HttpStatus;
use dev\winterframework\web\http\ResponseEntity;
use ReflectionMethod;

abstract class AdminController implements ControllerInterceptor {

    #[Autowired]
    public AdminLoginService $adminLoginService;

    public function preHandle(HttpRequest $request, ResponseEntity $response, ReflectionMethod $handler): bool {

        if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
            if (!$this->adminLoginService->verify($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'])) {
                $response->withStatus(HttpStatus::$UNAUTHORIZED)
                    ->withHeader('WWW-Authenticate', 'Basic realm="Exms Admin"')
                    ->setBody($response->getUnauthorizedBody());
                return false;
            }
        } else {
            $admin = $this->adminLoginService->sessionLogin($request);
            if (!$admin) {
                $response->withStatus(HttpStatus::$UNAUTHORIZED)
                    ->setBody($response->getUnauthorizedBody('Authentication required, Invalid session'));
                return false;
            }
        }

        return true;
    }

    public function postHandle(HttpRequest $request, ResponseEntity $response, ReflectionMethod $handler): void {
    }
}
