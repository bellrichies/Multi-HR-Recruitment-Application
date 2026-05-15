<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\HttpException;
use App\Core\Request;

class RateLimitMiddleware
{
    public function handle(Request $request, ?string $parameter = null): void
    {
        [$limit, $window] = array_pad(explode(',', $parameter ?? '60,60', 2), 2, '60');
        $limit = max(1, (int) $limit);
        $window = max(1, (int) $window);
        $key = sha1($request->ip() . '|' . $request->method() . '|' . $request->path());
        $file = base_path('storage/cache/rate_limit_' . $key . '.json');
        $now = time();
        $state = ['hits' => 0, 'reset_at' => $now + $window];

        if (is_file($file)) {
            $decoded = json_decode((string) file_get_contents($file), true);
            $state = is_array($decoded) ? $decoded : $state;
        }

        if (($state['reset_at'] ?? 0) <= $now) {
            $state = ['hits' => 0, 'reset_at' => $now + $window];
        }

        $state['hits']++;

        if (is_dir(dirname($file))) {
            file_put_contents($file, json_encode($state, JSON_THROW_ON_ERROR), LOCK_EX);
        }

        if ($state['hits'] > $limit) {
            throw new HttpException('Too many requests. Please try again later.', 429);
        }
    }
}
