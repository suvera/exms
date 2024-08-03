<?php

declare(strict_types=1);

namespace Gemini\Enums;

enum ModelType: string {
    case GEMINI_PRO = 'models/gemini-1.5-pro';
    case GEMINI_PRO_VISION = 'models/gemini-pro-vision';
    case EMBEDDING = 'models/embedding-001';
}
