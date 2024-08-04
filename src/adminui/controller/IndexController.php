<?php

namespace dev\suvera\exms\adminui\controller;

use dev\winterframework\stereotype\RestController;
use dev\winterframework\stereotype\web\GetMapping;
use dev\winterframework\web\view\HtmlTemplateView;
use dev\winterframework\web\view\View;
use dev\suvera\exms\adminui\view\LoginTemplate;
use dev\winterframework\web\http\HttpRequest;
use dev\suvera\exms\admin\service\AdminLoginService;
use dev\suvera\exms\utils\Utility;
use dev\winterframework\exception\HttpRestException;
use dev\winterframework\stereotype\Autowired;
use dev\winterframework\stereotype\web\PostMapping;
use dev\winterframework\stereotype\web\RequestParam;
use dev\winterframework\web\http\HttpStatus;

#[RestController]
class IndexController {
    #[Autowired]
    public AdminLoginService $adminLoginService;

    #[GetMapping(path: "/admin/login")]
    public function adminLogin(
        HttpRequest $request,
        #[RequestParam(source: 'get')] string $error = ''
    ): View {
        if ($this->adminLoginService->sessionLogin($request)) {
            Utility::headerRedirectAndExist('/admin/home');
        }

        $tpl = new LoginTemplate([
            'request' => $request,
            'pageTitle' => 'Admin Login',
            'error' => $error === '1' ? 'Invalid username or password' : ''
        ]);

        return new HtmlTemplateView($tpl);
    }

    #[PostMapping(path: "/admin/login")]
    public function doAdminLogin(
        HttpRequest $request,
        #[RequestParam(source: 'post')] string $username,
        #[RequestParam(source: 'post')] string $password
    ): void {
        if ($this->adminLoginService->sessionLogin($request)) {
            throw new HttpRestException(HttpStatus::$UNAUTHORIZED, 'Already logged in, Go to admin home.');
        }

        if ($this->adminLoginService->loginInitSession($username, $password)) {
            Utility::headerRedirectAndExist('/admin/home');
        }
        Utility::headerRedirectAndExist('/admin/login?error=1');
    }
}
