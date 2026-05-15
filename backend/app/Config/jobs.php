<?php

declare(strict_types=1);

return [
    'approval_required' => env('JOB_APPROVAL_REQUIRED', true),
    'public_statuses' => ['published', 'open', 'assigned'],
    'terminal_statuses' => ['closed', 'cancelled', 'filled'],
    'max_per_page' => 100,
];
