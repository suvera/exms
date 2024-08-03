<?php

namespace dev\suvera\exms\utils;

use dev\winterframework\stereotype\Component;
use dev\winterframework\stereotype\Value;
use dev\winterframework\util\log\Wlf4p;
use Gemini\Client as GeminiAi;
use Gemini as Gemini;
use Gemini\Resources\GenerativeModel;
use Spiral\JsonSchemaGenerator\Attribute\Field;
use Spiral\JsonSchemaGenerator\Generator;

/*
* question
* options
* answer
* explanation
* course_topic
* possible_solution_time_seconds
*/

class QuestionOption {
    public function __construct(
        #[Field(title: 'option A', description: 'Option A')]
        public readonly string $a,
        #[Field(title: 'option B', description: 'Option B')]
        public readonly string $b,
        #[Field(title: 'option C', description: 'Option C')]
        public readonly string $c,
        #[Field(title: 'option D', description: 'Option D')]
        public readonly string $d
    ) {
    }
}
class Question {
    public function __construct(
        #[Field(title: 'question', description: 'Question')]
        public readonly string $question,
        #[Field(title: 'answer', description: 'Answer')]
        public readonly string $answer,
        #[Field(title: 'explanation', description: 'Explanation')]
        public readonly string $explanation,
        #[Field(title: 'course_topic', description: 'Course Topic')]
        public readonly string $course_topic,
        #[Field(title: 'possible_solution_time_seconds', description: 'Possible Solution Time in Seconds')]
        public readonly int $possible_solution_time_seconds,
        #[Field(title: 'options', description: 'Options')]
        /**
         * @var array<QuestionOption>
         */
        public readonly array $options
    ) {
    }
}

class Questions {
    public function __construct(
        #[Field(title: 'questions', description: 'Questions')]
        /**
         * @var array<Question>
         */
        public readonly array $questions
    ) {
    }
}

#[Component]
class GeminiClient {
    use Wlf4p;

    const BATCH_SIZE = 10;
    private GeminiAi $gemini;
    private GenerativeModel $geminiModel;

    #[Value('${geminiai.apiKey}')]
    private string $apiKey = "";

    private string $promptInitial = <<<EOT
Generate {QUESTION_COUNT} random multiple choice Questions for below classes:
{CLASS} 

in below subjects: 
{SUBJECT}

in below topics:
{TOPIC}
{CHAPTERS}
---
Please add difficult & toughest questions. Please include answer with detailed explanation in the output.
Please provide option key value in the "answer" field. Please do not repeat questions. Please use HTML tags to beatify the question and options to display them beatifully.
EOT;

    private string $promptRepeatMore = 'Please generate {QUESTION_COUNT} more';

    public function __construct() {
        $this->gemini = Gemini::factory()
            ->withApiKey($this->apiKey)
            ->withBaseUrl('https://generativelanguage.googleapis.com/v1beta/')
            ->make();
        //$response = $this->gemini->models()->list();
        //print_r($response->models);
        //exit;

        $this->geminiModel = $this->gemini->geminiPro();
        $config = new Gemini\Data\GenerationConfig();
        $config->responsMimeType = 'application/json';

        $generator = new Generator();
        $schema = $generator->generate(Questions::class);
        //$config->responseSchema = json_encode($schema->jsonSerialize());
        $this->promptInitial .= "\nYou should output json using below JSON schema (but exclude JSON schema from output):\n" . json_encode($schema->jsonSerialize(), JSON_PRETTY_PRINT);
        $this->geminiModel->withGenerationConfig($config);
    }

    public function generateQuestions(int $questionCount, array $classes, array $subjects, array $topics, array $chapters): array {
        $prompt = $this->promptInitial;

        $classString = "*" . implode("\n* ", $classes);
        $subjectString = "*" . implode("\n* ", $subjects);
        $topicString = "*" . implode("\n* ", $topics);
        $chapterString = '';
        if (empty($chapters)) {
            $chapterString = "\nin below chapters:\n*" . implode("\n* ", $chapters);
        }

        $prompt = str_replace(
            ['{CLASS}', '{SUBJECT}', '{TOPIC}', '{CHAPTERS}'],
            [$classString, $subjectString, $topicString, $chapterString],
            $prompt
        );

        $finalJson = '';
        $data = [];
        if ($questionCount > self::BATCH_SIZE) {
            $chat = $this->geminiModel->startChat();
            do {
                $response = $chat->sendMessage($prompt);
                $code = $response->text();
                $dataPart = $this->parseJson($code);
                $data = array_merge($data, $dataPart);
                $questionCount -= self::BATCH_SIZE;
                $nextBatch = self::BATCH_SIZE;
                if ($questionCount < self::BATCH_SIZE) {
                    $nextBatch = $questionCount;
                }
                $prompt = str_replace('{QUESTION_COUNT}', $nextBatch, $this->promptRepeatMore);
            } while ($questionCount > 0);
        } else {
            $prompt = str_replace('{QUESTION_COUNT}', $questionCount, $prompt);
            $response = $this->geminiModel->generateContent($prompt);
            $finalJson = $response->text();
            $data = $this->parseJson($finalJson);
        }
        return $data;
    }

    protected function parseJson(string $jsonStr): array {
        //$jsonStr = preg_replace('/^\h*\/\/.*$/m', '', $jsonStr);
        //print_r($jsonStr);
        try {
            $json = json_decode($jsonStr, true, JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $msg = 'Error in parsing JSON' . json_last_error_msg();
                echo "$msg\n";
                self::logError($msg, ['json' => $jsonStr]);
                return [];
            }
            return $json;
        } catch (\Throwable $e) {
            $msg = 'Error in parsing JSON' . $e->getMessage();
            echo "$msg\n";
            self::logError($msg, ['error' => $e]);
            return [];
        }
    }
}
