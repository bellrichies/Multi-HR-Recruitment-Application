<?php

declare(strict_types=1);

return [
    'jwt_secret' => env('JWT_SECRET', 'change_this_secret'),
    'jwt_ttl' => (int) env('JWT_TTL', 3600),
    'jwt_refresh_ttl' => (int) env('JWT_REFRESH_TTL', 604800),
];
