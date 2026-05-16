<?php

declare(strict_types=1);

namespace App\Support\Cache;

class FileCache
{
    public function remember(string $key, int $ttlSeconds, callable $callback): mixed
    {
        if ((string) env('CACHE_DRIVER', 'file') !== 'file') {
            return $callback();
        }

        $path = $this->path($key);

        if (is_file($path)) {
            $payload = json_decode((string) file_get_contents($path), true);

            if (is_array($payload) && (int) ($payload['expires_at'] ?? 0) > time()) {
                return $payload['value'] ?? null;
            }
        }

        $value = $callback();
        $this->put($key, $value, $ttlSeconds);

        return $value;
    }

    public function put(string $key, mixed $value, int $ttlSeconds): void
    {
        $directory = base_path('storage/cache');

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        file_put_contents($this->path($key), json_encode([
            'expires_at' => time() + max(1, $ttlSeconds),
            'value' => $value,
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES), LOCK_EX);
    }

    public function forget(string $key): void
    {
        $path = $this->path($key);

        if (is_file($path)) {
            unlink($path);
        }
    }

    private function path(string $key): string
    {
        return base_path('storage/cache/cache_' . sha1($key) . '.json');
    }
}
