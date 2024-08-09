<?php

declare(strict_types=1);

namespace dev\suvera\exms\student\rest;

use dev\winterframework\core\web\ControllerInterceptor;
use dev\winterframework\web\http\HttpRequest;
use dev\winterframework\web\http\ResponseEntity;
use ReflectionMethod;

abstract class BaseController implements ControllerInterceptor {

    public function preHandle(HttpRequest $request, ResponseEntity $response, ReflectionMethod $handler): bool {

        $origin = $request->getFirstHeader('Origin');
        // Check if it's a CORS request
        if ($origin) {
            // Check if the origin is 'localhost' with port, with scheme
            if (preg_match('/^https?:\/\/localhost(:\d+)?$/', $origin)) {
                header("Access-Control-Allow-Origin: $origin");
                header('Access-Control-Allow-Credentials: true');
                header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
                header('Access-Control-Allow-Headers: *');
                // If it's a preflight request (OPTIONS method), exit early
                if ($request->getMethod() === 'OPTIONS') {
                    exit;
                }
            }
        }

        return true;
    }

    public function postHandle(HttpRequest $request, ResponseEntity $response, ReflectionMethod $handler): void {
    }
}
