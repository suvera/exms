<?php

namespace dev\suvera\exms\utils;

class ListResponse implements \JsonSerializable {
    public string $message = 'List retrieved successfully';

    public int $status =  200;

    public int $count = 0;

    public int $offset = 0;

    public int $limit = 10;

    public array $data = [];

    public function __construct() {
    }

    public function jsonSerialize(): array {
        return [
            'timestamp' => gmdate('Y-m-d\TH:i:s\Z'),
            'message' => $this->message,
            'status' => $this->status,
            'count' => $this->count,
            'offset' => $this->offset,
            'limit' => $this->limit,
            'data' => $this->data
        ];
    }
}
