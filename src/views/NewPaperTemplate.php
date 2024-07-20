<?php

declare(strict_types=1);

namespace dev\suvera\exms\views;

use dev\winterframework\web\view\HtmlTemplate;

class NewPaperTemplate extends HtmlTemplate {

    public function __construct(array $models = []) {
        parent::__construct(
            __DIR__ . '/files/simple/header.phtml',
            __DIR__ . '/files/simple/footer.phtml',
            __DIR__ . '/files/simple/new_paper.phtml',
            $models
        );
    }

    public function setContentFile(string $contentFile): void {
        $this->contentFile = __DIR__ . '/files/simple/' . $contentFile . '.phtml';
    }
}
