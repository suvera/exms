<?php

declare(strict_types=1);

namespace dev\suvera\exms\adminui\controller;

use dev\suvera\exms\admin\service\AdminLoginService;
use dev\suvera\exms\adminui\view\AdminTemplate;
use dev\suvera\exms\data\entity\Admin;
use dev\suvera\exms\utils\Utility;
use dev\winterframework\core\context\ApplicationContext;
use dev\winterframework\core\web\ControllerInterceptor;
use dev\winterframework\stereotype\Autowired;
use dev\winterframework\web\http\HttpRequest;
use dev\winterframework\web\http\ResponseEntity;
use dev\winterframework\web\view\HtmlTemplateView;
use dev\winterframework\web\view\View;

abstract class AdminController implements ControllerInterceptor {

    #[Autowired]
    public AdminLoginService $adminLoginService;

    #[Autowired]
    public ApplicationContext $ctx;

    public function preHandle(HttpRequest $request, ResponseEntity $response, \ReflectionMethod $handler): bool {
        $student = $this->adminLoginService->sessionLogin($request);
        if (!$student) {
            Utility::headerRedirectAndExist('/admin/login?error=2');
            // $response->withStatus(HttpStatus::$UNAUTHORIZED)
            //     ->setBody($response->getUnauthorizedBody('Authentication required, Invalid session'));
            return false;
        }

        return true;
    }

    public function postHandle(HttpRequest $request, ResponseEntity $response, \ReflectionMethod $handler): void {
    }

    protected function getAdmin(): Admin {
        $admin = new Admin();
        $admin->__unserialize($_SESSION['admin']);
        return $admin;
    }

    protected function errorPage(string $message): View {
        $model = [
            'pageTitle' => 'Error',
            'message' => $message,
            'description' => ''
        ];

        $tpl = new AdminTemplate($this->ctx, 'simple/error', $model);

        return new HtmlTemplateView($tpl);
    }
}
