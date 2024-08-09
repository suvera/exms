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
use dev\winterframework\stereotype\Autowired;
use dev\winterframework\stereotype\web\PostMapping;
use dev\winterframework\stereotype\web\RequestParam;

#[RestController]
class IndexController {
    #[Autowired]
    public AdminLoginService $adminLoginService;

    #[GetMapping(path: "/admin/login")]
    public function adminLogin(
        HttpRequest $request,
        #[RequestParam(source: 'get', required: false)] string $error = ''
    ): View {
        if ($this->adminLoginService->sessionLogin($request)) {
            Utility::headerRedirectAndExist('/admin/home');
        }

        $err = '';
        switch ($error) {
            case '1':
                $err = 'Invalid username or password';
                break;
            case '2':
                $err = 'Authentication required, Invalid session';
                break;
        }

        $tpl = new LoginTemplate([
            'request' => $request,
            'pageTitle' => 'Admin Login',
            'error' => $err
        ]);

        return new HtmlTemplateView($tpl);
    }

    #[PostMapping(path: "/admin/login")]
    public function doAdminLogin(
        HttpRequest $request,
        #[RequestParam(source: 'post')] string $username,
        #[RequestParam(source: 'post')] string $password
    ): void {
        if ($this->adminLoginService->loginInitSession($username, $password)) {
            Utility::headerRedirectAndExist('/admin/home');
        }
        Utility::headerRedirectAndExist('/admin/login?error=1');
    }

    #[GetMapping(path: "/admin/logout")]
    public function doAdminLogout(
        HttpRequest $request
    ): void {
        $this->adminLoginService->sessionLogout($request);
        Utility::headerRedirectAndExist('/admin/login');
    }
}
