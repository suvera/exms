<?php

declare(strict_types=1);

namespace dev\suvera\exms\data;

enum ExamPaperStatus: string {
    case PREPARING = 'preparing';
    case FREEZED = 'freezed';

    public function isPreparing(): bool {
        return $this->equals(self::PREPARING);
    }

    public function isFreezed(): bool {
        return $this->equals(self::FREEZED);
    }

    public function equals(ExamPaperStatus $status): bool {
        return $this->value === $status->value;
    }

    public function getKeyValues(): array {
        return [
            'preparing' => 'preparing',
            'freezed' => 'freezed'
        ];
    }
}
