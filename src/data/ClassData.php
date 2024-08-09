<?php

namespace dev\suvera\exms\data;

class ClassData {
    private static array $data = [
        '8-CBSE' => [
            'name' => '8-CBSE',
            'board' => 'CBSE',
        ],
        '8-SSE' => [
            'name' => '8-SSE',
            'board' => 'SSE',
        ],
        '9-CBSE' => [
            'name' => '9-CBSE',
            'board' => 'CBSE',
        ],
        '9-SSE' => [
            'name' => '9-SSE',
            'board' => 'SSE',
        ],
        '10-CBSE' => [
            'name' => '10-CBSE',
            'board' => 'CBSE',
        ],
        '10-SSE' => [
            'name' => '10-SSE',
            'board' => 'SSE',
        ],
        '11-CBSE' => [
            'name' => '11-CBSE',
            'board' => 'CBSE',
        ],
        // BIE = Board of Intermediate Education
        '11-BIE' => [
            'name' => '11-BIE',
            'board' => 'BIE',
        ],
        '12-CBSE' => [
            'name' => '12-CBSE',
            'board' => 'CBSE',
        ],
        '12-BIE' => [
            'name' => '12-BIE',
            'board' => 'BIE',
        ],
    ];

    private static array $emptyArray = [];

    public static function getClass(string $id): array {
        if (isset(self::$data[$id])) {
            self::$data[$id]['id'] = $id;
            return self::$data[$id];
        }

        return self::$emptyArray;
    }

    public static function getClassName(string $id): string {
        if (isset(self::$data[$id])) {
            return self::$data[$id]['name'];
        }

        return '';
    }

    public static function hasClass(string $id): bool {
        return isset(self::$data[$id]);
    }

    public static function getClasses(): array {
        return self::$data;
    }

    public static function getClassIdNames(): array {
        $data = [];
        foreach (self::$data as $id => $class) {
            $data[$id] = $class['name'];
        }
        return $data;
    }
}
