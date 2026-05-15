<?php

declare(strict_types=1);

return [
    'currency' => env('WALLET_CURRENCY', 'NGN'),
    'candidate_unlock_fee' => (float) env('CANDIDATE_UNLOCK_FEE', 5000),
    'candidate_unlock_days' => (int) env('CANDIDATE_UNLOCK_DAYS', 30),
];
