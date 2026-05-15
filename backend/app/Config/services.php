<?php

declare(strict_types=1);

return [
    'mail' => [
        'host' => env('MAIL_HOST'),
        'port' => (int) env('MAIL_PORT', 587),
        'username' => env('MAIL_USERNAME'),
        'password' => env('MAIL_PASSWORD'),
        'from_address' => env('MAIL_FROM_ADDRESS', 'no-reply@example.com'),
        'from_name' => env('MAIL_FROM_NAME', 'Multi HR Platform'),
    ],
    'paystack' => [
        'public_key' => env('PAYSTACK_PUBLIC_KEY'),
        'secret_key' => env('PAYSTACK_SECRET_KEY'),
        'callback_url' => env('PAYSTACK_CALLBACK_URL'),
        'webhook_secret' => env('PAYSTACK_WEBHOOK_SECRET'),
    ],
];
