<?php

declare(strict_types=1);

namespace dev\suvera\exms\data;

enum ExamPaperStatus: string {
    case PREPARING = 'preparing';
    case FREEZED = 'freezed';
}
