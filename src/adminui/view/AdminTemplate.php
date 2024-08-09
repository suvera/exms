<?php

namespace dev\suvera\exms\adminui\view;

use dev\suvera\exms\admin\service\AdminLoginService;
use dev\winterframework\core\context\ApplicationContext;
use dev\winterframework\web\view\HtmlTemplate;

class AdminTemplate extends HtmlTemplate {

    public function __construct(
        private ApplicationContext $ctx,
        string $view,
        array $models = []
    ) {
        if (!isset($models['pageTitle'])) {
            $models['pageTitle'] = 'Admin';
        }
        /** @var AdminLoginService $loginService */
        $loginService = $this->ctx->beanByClass(AdminLoginService::class);
        $models['adminName'] = $loginService->getAdmin()->name;

        parent::__construct(
            __DIR__ . '/files/simple/header.phtml',
            __DIR__ . '/files/simple/footer.phtml',
            __DIR__ . '/files/' . $view . '.phtml',
            $models
        );
    }
}
