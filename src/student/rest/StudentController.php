<?php

declare(strict_types=1);

namespace dev\suvera\exms\student\rest;

use dev\suvera\exms\student\service\LoginService;
use dev\winterframework\stereotype\Autowired;
use dev\winterframework\web\http\HttpRequest;
use dev\winterframework\web\http\HttpStatus;
use dev\winterframework\web\http\ResponseEntity;
use ReflectionMethod;

abstract class StudentController extends BaseController {

    #[Autowired]
    public LoginService $loginService;

    public function preHandle(HttpRequest $request, ResponseEntity $response, ReflectionMethod $handler): bool {
        if (!parent::preHandle($request, $response, $handler)) {
            return false;
        }
        if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
            if (!$this->loginService->verify($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'])) {
                $response->withStatus(HttpStatus::$UNAUTHORIZED)
                    ->withHeader('WWW-Authenticate', 'Basic realm="Exms Student"')
                    ->setBody($response->getUnauthorizedBody());
                return false;
            }
        } else {
            // token based authentication
            $student = $this->loginService->sessionLogin($request);
            if (!$student) {
                $response->withStatus(HttpStatus::$UNAUTHORIZED)
                    ->setBody($response->getUnauthorizedBody('Authentication required, Invalid token'));
                return false;
            }
        }

        return true;
    }

    public function postHandle(HttpRequest $request, ResponseEntity $response, ReflectionMethod $handler): void {
    }
}
