<?php

declare(strict_types=1);

namespace dev\suvera\exms\service;

use dev\winterframework\stereotype\Service;

#[Service]
class PaperService {

    public function createPaper(string $name): int {
        $id = mt_rand(1000, PHP_INT_MAX);
        $folder = 'exms_' . $id;
        mkdir('/tmp/' . $folder, 0777, true);
        $data = [
            'name' => $name,
            'questions' => []
        ];
        file_put_contents('/tmp/' . $folder . '/paper.json', json_encode($data, JSON_PRETTY_PRINT));

        return $id;
    }

    public function getPaper(int $paperId): array {
        $folder = 'exms_' . $paperId;
        $data = file_get_contents('/tmp/' . $folder . '/paper.json');
        if (empty($data)) {
            return [];
        }
        try {
            return json_decode($data, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            return [];
        }
    }

    // addQuestions
    public function addQuestions(int $paperId, array $questions): string {
        $folder = 'exms_' . $paperId;
        $data = $this->getPaper($paperId);
        foreach ($questions as $idx => $question) {
            // question, options, answer, explanation, course_topic, possible_solution_time_seconds
            if (empty($question['question'])) {
                return 'Question is Empty for ' . $idx + 1;
            }
            if (empty($question['options'])) {
                return 'Options are Empty for ' . $idx + 1;
            }
            if (!isset($question['answer'])) {
                return 'Answer is Empty for ' . $idx + 1;
            }

            $data['questions'][] = $question;
        }
        file_put_contents('/tmp/' . $folder . '/paper.json', json_encode($data, JSON_PRETTY_PRINT));
        return '';
    }
}
