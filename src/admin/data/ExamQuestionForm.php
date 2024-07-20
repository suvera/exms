<?php

declare(strict_types=1);

namespace dev\suvera\exms\admin\data;

use dev\winterframework\stereotype\JsonProperty;

class ExamQuestionForm {
    #[JsonProperty(required: true, validate: [['len', 'max' => 65535]])]
    public string $question;

    #[JsonProperty(required: true, validate: [['len', 'max' => 65535]])]
    public string $explanation;

    #[JsonProperty(name: "possible_solution_time_seconds", required: true, validate: [['int', 'min' => 1]])]
    public int $timeSeconds;

    #[JsonProperty(name: "options", required: true)]
    public QuestionOptionForm $options;

    #[JsonProperty(name: "answer", required: true, validate: [['oneOf', 'values' => ['a', 'b', 'c', 'd']]])]
    public string $answer;

    #[JsonProperty(name: "course_topic", required: true, validate: [['len', 'max' => 1024]])]
    public string $topic;
}
/*
[
    {
        "question": "Let a and b be two irrational numbers. Which of the following statements is NOT always true?",
        "options": {
            "a": "a + b is irrational.",
            "b": "a - b is irrational.",
            "c": "a * b is irrational.",
            "d": "a / b is irrational."
        },
        "answer": "a",
        "explanation": "Consider the following counterexample: \u221a2 + (- \u221a2) = 0, which is rational. The other options are always true because the sum, difference, product, and quotient of two irrational numbers (with a non-zero denominator) will always result in an irrational number.",
        "course_topic": "Irrational Numbers",
        "possible_solution_time_seconds": 120
    },
    {
        "question": "If x = 9^(1/3) * 27^(-1/9), then the value of x^2 is:",
        "options": {
            "a": "1",
            "b": "3",
            "c": "9",
            "d": "27"
        },
        "answer": "b",
        "explanation": "We have, x = 9^(1/3) * 27^(-1/9) = (3^2)^(1/3) * (3^3)^(-1/9) = 3^(2/3) * 3^(-1/3) = 3^(2/3 - 1/3) = 3^(1/3). Therefore, x^2 = (3^(1/3))^2 = 3^(2/3) = 3.",
        "course_topic": "Laws of Exponents for Real Numbers",
        "possible_solution_time_seconds": 90
    }
]
*/