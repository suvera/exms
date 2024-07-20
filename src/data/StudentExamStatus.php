<?php

namespace dev\suvera\exms\data;

enum StudentExamStatus: string {
    case PENDING = 'pending';
    case IN_PROGRESS = 'started';
    case COMPLETED = 'completed';
}
