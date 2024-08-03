<?php

use dev\suvera\exms\data\entity\Admin;
use dev\suvera\exms\utils\EntityGenerator;
use dev\suvera\exms\utils\GeminiClient;
use dev\winterframework\core\app\ApplicationReadyEvent;
use dev\winterframework\core\app\WinterCliApplication;
use dev\winterframework\core\context\ApplicationContext;
use dev\winterframework\stereotype\Autowired;
use dev\winterframework\stereotype\Component;
use dev\winterframework\stereotype\OnApplicationReady;
use dev\winterframework\stereotype\txn\EnableTransactionManagement;
use dev\winterframework\stereotype\WinterBootApplication;

require_once dirname(__DIR__) . '/vendor/autoload.php';

#[WinterBootApplication(
    // List of config directories
    configDirectory: ['../config'],

    // array of records in format [NamespacePrefix, BaseDirectory]
    scanNamespaces: [['dev\\suvera\\exms', '../src/']],
)]
#[EnableTransactionManagement]
#[OnApplicationReady]
#[Component]
class ExampleApplication implements ApplicationReadyEvent {
    #[Autowired]
    private ApplicationContext $appCtx;

    public static function main() {
        $app = new WinterCliApplication();
        $app->run(ExampleApplication::class);
    }

    public function onApplicationReady(): void {
        global $argv;
        echo "Applicated Started!\n";
        $em = $this->appCtx->beanByName('default-doctrine-em');
        $em->getConnection()->getDatabasePlatform()->registerDoctrineTypeMapping('set', 'string');
        $em->getConnection()->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');

        $cmd = isset($argv[1]) ? $argv[1] : '';
        if ($cmd === 'generate-entities') {
            $generator = new EntityGenerator($em, dirname(__DIR__) . '/src/data/entity', 'dev\\suvera\\exms\\data\\entity');
            $generator->generate();
        } else if ($cmd === 'create-admin') {
            $faker = Faker\Factory::create();
            $admin = new Admin();
            $admin->name = $faker->name();
            $admin->username = $faker->userName();
            $pwd = $faker->password(10, 14);
            $admin->password = password_hash($pwd, PASSWORD_BCRYPT);
            $em->persist($admin);
            $em->flush();
            echo PHP_EOL . 'Admin created - Username: ' . $admin->username . ', Password: ' . $pwd . '' . PHP_EOL;
        } else if ($cmd === 'delete') {
            //$em->getConnection()->executeStatement('delete from sessions where session_expires < ?', [(new \DateTime('now'))->format('Y-m-d H:i:s')]);
            $em->getConnection()->delete('sessions', ['session_id' => '2']);
            echo PHP_EOL . 'Deleted expired sessions' . PHP_EOL;
        } else if ($cmd === 'gemini') {
            /** @var GeminiClient $gemini */
            $gemini = $this->appCtx->beanByClass(GeminiClient::class);
            $questions = $gemini->generateQuestions(3, ['9-CBSE', '9-SSE'], ['Maths'], ['Algebra'], []);
            print_r($questions);
        } else {
            print PHP_EOL . 'Unknown command: ' . $cmd . PHP_EOL;
        }

        print PHP_EOL . 'Done!' . PHP_EOL;
    }
}

chdir(__DIR__);

ExampleApplication::main();
