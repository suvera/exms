<?php

namespace dev\suvera\exms;

use dev\winterframework\core\app\WinterWebApplication;
use dev\winterframework\stereotype\WinterBootApplication;

#[WinterBootApplication(
    configDirectory: [__DIR__ . "/../config"],
    scanNamespaces: [
        ['dev\\suvera\\exms', __DIR__ . '']
    ]
)]
class ExmsApplication {

    public static function main(): void {
        $winterApp = new WinterWebApplication();
        $winterApp->run(self::class);
    }
}
